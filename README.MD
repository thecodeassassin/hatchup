# Hatchup
Simple meetup.com clone

Author: Stephen "TheCodeAssassin" Hoogendijk

# How to install

## Testing/Development

Local testing will be done using Vagrant. Vagrant will spin up a machine (either locally or remotely) and use that
instance to serve your code.

*Note* You need at least Vagrant 1.8.1

```
$ vagrant up
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
I created a script to make it easier to interact with terraform.

*Note* This step might include the consumption of coffee/tea since it WILL take a while.

The script will do two things when running apply:

1. Create the infrastructure in AWS
2. Provision the infrastructure

```

# now apply the infrastructure
$ bash terraform/run.sh apply stephen ~/.ssh/stephen.pub

```

### Destroying the infrastructure

Should you need to destroy all the managed infrastructure (this is considered catastrophic in a live scenario). You need
to run the same script as above but using the destroy operation.

```
$ bash terraform/run.sh destroy stephen ~/.ssh/stephen.pub
```


## What i would do to improve this

* Not put everything in one repo but separate it properly and on a self-hosted Gitlab or Stash
* Add more features of course
* Maybe use ElasticSearch Shield for additional security should the VPC be breached
* Improve ansible playbooks
* Installing the application via a CI instead of copy with ansible
* Write unit tests for the application

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

# Infrastructure build

The following infrastructure will be build by the terraform configuration:

* A VPC with a gateway and 2 subnets (one per availability zone)
* 2 ELB Loadbalancers (one for the application and one for the elasticsearch cluster)
* 2 Elasticsearch instances (configurable)
* 2 Application instances (configurable)
* VPC Security group (for allowing traffic within the VPC)
* Security group for the Elasticsearch cluster
* Security group for the application cluster

# Monitoring?



# What about security?

We create a VPC which only exposes port 80 and port 22 to the outside. The elasticsearch nodes are not reachable
via the Internet only from instances inside the VPC.

