<?php

    define('DOCUMENT_ROOT', dirname(__FILE__));
    require_once DOCUMENT_ROOT . '/vendor/autoload.php';

    $environment = $argc == 2 ? $argv[1] : 'staging';
    $config_file = DOCUMENT_ROOT . "/config/$environment.yml";

    if(!file_exists($config_file)) throw new UnexpectedValueException('Unknown environment');

    $twig = new Twig_Environment(new Twig_Loader_Filesystem(DOCUMENT_ROOT . '/tpls'), ['debug' => true]);
    $twig->addExtension(new Twig_Extension_Debug());

    $configs = Symfony\Component\Yaml\Yaml::parse(file_get_contents($config_file));

    $manager_content = @json_decode(file_get_contents(tuneUrl($configs['manager_url'], $configs['credentials'])), true);

    $params['haproxy_ip'] = $configs['haproxy_ip'];
    $params['mailer_ip'] = $configs['mailer_ip'];
    $params['global'] = $configs['global'];
    $params['title'] = $configs['title'];
    $params['backends'] = $containers = [];

    if(!$configs['is_rancher']) {
        if(isset($manager_content['node']['nodes'])) {
            foreach ($manager_content['node']['nodes'] as $node) {
                if (preg_match('/^\/[\w\-]+\/([\w\-]+):([^_]+)\w+([^:]+)/', $node["key"], $matches)) {
                    $containers[$matches['2']][] = ['name' => "{$matches['1']}-c{$matches['3']}",
                        'host' => $node["value"], 'port' => parse_url($node["value"], PHP_URL_PORT)];
                }
            }
        }
    } else {
        foreach($manager_content['data'] as $data) {
            $appName = strtolower(preg_replace("/[^a-z]/i",'',$data['name']));
            $services_content = @json_decode(file_get_contents(tuneUrl($data['links']['services'], $configs['credentials'])), true);

            foreach($services_content['data'] as $data) {
                if(isset($data['publicEndpoints'])) {
                    foreach ((array)$data['publicEndpoints'] as $endPoints) {
                        $containers[$appName][] = ['name' => "{$endPoints['instanceId']}-{$endPoints['hostId']}",
                            'host' => "{$endPoints['ipAddress']}:{$endPoints['port']}", 'port' => $endPoints['port']];
                    }

                    if(isset($data["launchConfig"]["labels"]["com.letsbonus.subdomains"])
                        && isset($data["launchConfig"]["labels"]["com.letsbonus.healthcheck"])){
                        $subdomains = json_decode($data["launchConfig"]["labels"]["com.letsbonus.subdomains"], true);
                        $healthcheck = json_decode($data["launchConfig"]["labels"]["com.letsbonus.healthcheck"], true);

                        appendApplication($configs['frontend'], $configs['backend'], $appName, $subdomains, $healthcheck, $environment);
                    }
                }
            }
        }
    }

    foreach ($containers as $name => $container)
    {
        if( array_key_exists( $name, $configs['backend'] ) ) $params['backends'][$name] = $twig->render('backend.twig',
            array_merge($configs['backend'][$name], ['name' => $name, 'servers' => $container]));
    }

    $params['frontends'] = [];

    foreach ($configs['frontend'] as $frontend) {
        $new_backends = [];
        foreach($frontend['backends'] as $backend){
            if( array_key_exists( $backend['name'], $params['backends'] ) ) $new_backends[] = $backend;
        }
        $frontend['backends'] = $new_backends;
        $params['frontends'][] = $twig->render('frontend.twig', $frontend);
    }

    exit ((int)!@file_put_contents( $configs['store_path'], $twig->render('layout.twig', $params)));
    
    function tuneUrl($url, $credentials) {
        return !empty($credentials) ? str_replace('://', "://$credentials@", $url) : $url;
    }

    function appendApplication(array &$frontends, array &$backends, $appName, array $subdomains, array $healthcheck, $environment = 'staging'){
        foreach($frontends as &$frontend){
            foreach($subdomains as $name => $arrSubdomain) {
                if ($frontend["name"] == $name) {
                    foreach ($arrSubdomain as $subdomain) {
                        if ($environment == 'staging-test') {
                            $subdomain = str_replace('staging', 'staging-test', $subdomain);
                        }
                        $frontend["backends"][] = array('domain' => $subdomain, 'name' => $appName);
                    }
                }
            }
        }

        $backends[$appName] = array('health_check' => $healthcheck['url']
        , 'mail_from' => $healthcheck['mail-from']
        , 'mail_to' => $healthcheck['mail-to']);
    }

