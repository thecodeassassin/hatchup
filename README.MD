# Hatchup
Simple meetup.com clone

Author: Stephen "TheCodeAssassin" Hoogendijk

# How to install

## Testing/Development

To test the application you would need Docker and Docker-Compose installed locally.

* Docker installation: https://docs.docker.com/engine/installation/ubuntulinux/
* Docker-compose installation: https://docs.docker.com/compose/install/

Then run:

```
$ docker-compose up
```

## To deploy the application stack to AWS

### The infra
First you would have to install Terraform and Ansible.

* Terraform: https://terraform.io/downloads.html
* Ansible: http://docs.ansible.com/ansible/intro_installation.html


You need to have a config.tf file so that Terraform can connect to your AWS account. Copy the config.tf.example file
and replace the access_key and secret_key values.

You need to provide a public key name and the path to your a local public key.
*Important*: The key needs to already be present in AWS EC2.

Obviously you need to replace "hatchup" with your own key name and path.

*Note* This step might include the consumption of coffee/tea since it WILL take a while
```

$ cd terraform

# first we need to update the terraform modules
$ terraform get

# now apply the infrastructure
$ terraform apply -var 'key_name=hatchup' -var 'public_key_path=/Users/hatchup/.ssh/hatchup.pub'

```

### The provisioning

Now that we have our infra ready we should start provisioning. For this we use Ansible.

Configure the AWS access and Secret key
```
$ export AWS_ACCESS_KEY_ID='123456'
$ export AWS_SECRET_ACCESS_KEY='bigsecret123'

```

Test the config:

```
./ec2.py --list
```


Now... run the provisioner(s), make some coffee (or tea).

```

$ ansible-playbook ansible/main.yml -i ec2.py -u ubuntu eu-central-1

```


## What i would do to improve this

* Replace the local sqlite user database with a MariaDB Galera cluster
* Not put everything in one repo but separate it properly and on a self-hosted Gitlab or Stash
* Add more features of course
* Maybe use ElasticSearch Shield for additional security should the VPC be breached

## Why did i pick these technologies?

* PHP because i have the most experience with it (scripting language wise)
* Ansible because it's simple and gets the job done quickly
* Terraform because it allows me to scale easily

I did not like the prospect of storing users in Elasticsearch because i do not believe users belong there.
If this would not be a simple MVP i would setup a proper MariaDB Galera cluster to host additional database requirements.

## Does it scale?

Sure, just add nodes in the main.tf file. The EBS should take care of routing the traffic to healthy instances should
your instance go down. Then re-run ansible to provision everything.

To scale up the web instances:
Edit "count = x" in the terraform/web-nodes.tf file.

# Monitoring?

The nodes are provisioned with Telegraf installed that will send data to the InfluxDB node.

# What about security?

We create a VPC which only exposes port 80 and port 22 to the outside. The elasticsearch nodes are not reachable
via the Internet only from instances inside the VPC.

