# Rancher Workshop

Letsbonus rancher environment replication.

## VM

* [Docker Machine][]

Check your docker machine version:

```
$ docker-machine --version
docker-machine version 0.8.2, build e18a919
```

## Rancher environment


1. Create a rancher master with a connection to mysql database.

```
# You can execute this on your primary docker-machine 
$ docker-compose up -d

```

2. You can acces now your rancher master on http://{docker-machine ip default}:8080

3. Create three new docker-machines to have our hosts

```
$ docker-machine create -d virtualbox rancher-host-1
$ docker-machine create -d virtualbox rancher-host-2
$ docker-machine create -d virtualbox rancher-host-3
```

4. Navigate to Infrastructure > Hosts, press "Add host" button:

	4.1 First time rancher will ask you to save the master ip, just press _Save_.

	4.2 Copy the docker run command in section 5.



5. SSH into hosts and run Rancher host command.

```
# Check IP, it's will use at next step
$ docker-machine ip rancher-host-1

# SSH and run agent command such as: sudo docker run -d ...
$ docker-machine ssh rancher-host-1
$ sudo docker run -d --privileged -v /var/run/docker.sock:/var/run/docker.sock -v /var/lib/rancher:/var/lib/rancher rancher/agent:v1.2.0 http://192.168.99.100:8080/v1/scripts/8EAF52490FDB555ACC54:1483142400000:2uTActl1J2JUVn9OHptZ6qy0U
```

6. Repeat on every host.

And now, you can see host in Rancher Page.



## Deploy a project

1. Navigate to Stacks > User page and push _Add stack_ button

2. Paste docker-compose.yml & rancher-compose.yml file provided in env/sepa directory.

3. Create it.


## Ha-proxy our deployed project

1. Lets create an API connection in Rancher:

	1.1 Navigate to API > Keys and press _Add account API Key_ button.

	1.2 Keep the generated tokens.

	1.3 In our project, open _ha-manager/config/test.yml_ file and replace keys with provided one.