# Magento2 sample data generator
## Description
The extension adds an additional CLI command to Magento 2 that allows to generate sample entities:
- Products
- Categories
- Customers
- Orders

It might be useful if you are going to test your store or a separate implementation with big amount of data.

## Installation
- Put the package contents to the `app/code` directory
- Clean Magento cache using built in CLI command `./bin/magento cache:clean`
- Enable the extension `./bin/magento module:enable Atwix_Samplegen`

## Usage
Use the following CLI commands to generate the sample entities:
```
./bin/magento samplegen:generate:products --count 1000
./bin/magento samplegen:generate:categories --count 1000
./bin/magento samplegen:generate:orders --count 1000
./bin/magento samplegen:generate:customers --count 1000
```
To remove the generated entities use the same command with `--removeall 1` parameter:
```
./bin/magento samplegen:generate:products --removeall 1
```
