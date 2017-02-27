# Rancher Workshop

Letsbonus rancher environment replication.

## VM

* [Docker Machine][]

Check your docker machine version:

```
$ docker-machine --version
docker-machine version 0.8.2, build e18a919
```

## First steps


1. Create a rancher master with a connection to mysql database.

```
# You can execute this on your primary docker-machine 
$ docker-compose up -d

```

2. You can acces now your rancher master on {docker-machine ip default}:8080

3. Create three new docker-machines to have our hosts

```
$ docker-machine create -d virtualbox rancher-host-1
$ docker-machine create -d virtualbox rancher-host-2
$ docker-machine create -d virtualbox rancher-host-3
```

4. 

# See http://<SERVER_IP>:8080/ and copy the command in custom host page
```

4. SSH into hosts and run Rancher Agent:

```
# Check IP, it's will use at next step
$ docker-machine ip rancher-host-1
$ docker-machine ip rancher-host-2
$ docker-machine ip rancher-host-3

# SSH and run agent command such as: sudo docker run -d ...
$ docker-machine ssh rancher-host-1
$ docker-machine ssh rancher-host-2
$ docker-machine ssh rancher-host-3
```

And now, you can see host in Rancher Page.

### Define

Here is a `docker-compose.yml` example for build wordpress:

```yml
wordpress:
  image: wordpress:4.6
  links:
    - mysql
  environment:
    WORDPRESS_DB_PASSWORD: example

mysql:
  image: mariadb:10.0
  environment:
    MYSQL_ROOT_PASSWORD: example
```

In my custom, I will add a *Load Balancer* for porting back-end service.

### Manage

Here is some examples to manage your service:

1. See STDOUT / STDERR
2. Upgrade service via Web UI
3. Upgrade service via Rancher Compose
4. Create new stack from legacy stack


## References

* [Rancher Ngork](https://github.com/jmcarbo/rancher-ngrok) , thx 默司 support.

[Docker Machine]: https://docs.docker.com/machine/
[Rancher]: http://rancher.com/
[VirtualBox]: https://www.virtualbox.org/
