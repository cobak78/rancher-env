# Rancher Workshop

Letsbonus rancher environment replication.

## Linux user

You must install docker-machine and virtualBox in order to follow this workshop

$ curl -L https://github.com/docker/machine/releases/download/v0.10.0/docker-machine-`uname -s`-`uname -m` >/tmp/docker-machine &&
  chmod +x /tmp/docker-machine &&
  sudo cp /tmp/docker-machine /usr/local/bin/docker-machine

$ sudo apt-get install virtualBox

## Rancher environment

1. Create a rancher master with a connection to mysql database.

```
# You can execute this on your primary docker-machine (linux user do this on your localhost as usual) 
$ docker-compose up -d

```

2. You can acces now your rancher master on http://{docker-machine ip default}:8080

3. Create two new docker-machines to run our hosts

```
$ docker-machine create rancher-host-1
$ docker-machine create rancher-host-2
```

4. On Rancher navigate to Infrastructure > Hosts, press "Add host" button:

	4.1 First time rancher will ask you to save the master ip, just press _Save_.

	4.1.x Linux user must use the host subnet ip here. try with 10.0.2.2 or use iptables to see it.

	4.2 Copy the docker run command showed here, you will need it in section 5.


5. Access via ssh to your hosts and run copied Rancher host command.

```
# Check IP, it's will use at next step
$ docker-machine ip rancher-host-1

# SSH and run agent command copied on step 4, something such as: sudo docker run -d ...
$ docker-machine ssh rancher-host-1
$ sudo docker run -d --privileged -v /var/run/docker.sock:/var/run/docker.sock -v /var/lib/rancher:/var/lib/rancher rancher/agent:v1.2.0 http://192.168.99.100:8080/v1/scripts/8EAF52490FDB555ACC54:1483142400000:2uTActl1J2JUVn9OHptZ6qy0U
```

6. Repeat on every host.

And now, you can see host in Rancher Page.



## Deploy a project

1. Navigate to Stacks > User page and push _Add stack_ button

2. Paste docker-compose.yml & rancher-compose.yml file provided in env/basic-go directory.

3. Create it.


## Ha-proxy our deployed project

1. Copy the ip and port generated for the deployed app.

2. Change it on the ha-proxy > haproxy.cfg file

3. Get up ha-proxy container with $ _docker-compose -f docker-compose-haproxy.yml up -d --build.

4. Navigate to your 8081 port to see ha-proxy in action with your deployed project.

4. You can also view your ha-proxy in http://192.168.99.100:1936/haproxy_stats with admin:admin login.