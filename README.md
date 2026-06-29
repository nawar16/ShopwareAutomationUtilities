
This repository contains independent Shopware extensions designed to connect a Shopware e-commerce store to self-hosted Python microservices.

![CI](https://github.com/nawar16/ShopwareAutomationUtilities/actions/workflows/ci.yml/badge.svg)


### **Prerequisite**
These extensions require the core microservices hosted in the companion repository: 
[Automation Utilities](https://github.com/nawar16/AutomationUtilities)

### **Available Plugins**

## LocalProductOptPlugin (Full-Stack UI Component): Features an integrated Admin configuration panel and an interactive Vue.js button injected directly into the Shopware Product Detail page

## TaxVatValidatorPlugin (CLI Automation): A pure backend engine designed with no user interface, running strictly via bin/console vat:validate or scheduled system cronjobs

### **Installation**
To install a specific extension, configure a Composer path repository in the Shopware installation, or copy the desired extension folder directly into the Shopware project directory:
```bash
cp -r ./[ExtensionName] /path-to-shopware/custom/plugins/[ExtensionName]
cd /path-to-shopware
bin/console plugin:refresh
bin/console plugin:install --activate [ExtensionName]
```


