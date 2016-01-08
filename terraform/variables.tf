variable "public_key_path" {
  description = <<DESCRIPTION
Path to the SSH public key to be used for authentication.
Ensure this keypair is added to your local SSH agent so provisioners can
connect.

Example: ~/.ssh/hatchup.pub
DESCRIPTION
}

variable "key_name" {
  description = "Desired name of AWS key pair"
}

variable "aws_region" {
  description = "AWS region to launch servers."
  default = "eu-central-1"
}

variable "instance_type" {
  default = "t2.micro"
}

# Ubuntu 14.04 LTS
variable "aws_amis" {
  default = {
    eu-central-1 = "ami-accff2b1"
  }
}

variable "mysql_root_password" {
  description = "The desired mysql root password"
}