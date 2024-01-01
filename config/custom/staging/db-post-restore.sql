## Post-database restore queries
#
# The following queries are executed automatically by `scripts/db-restore.sh`
# after the database dump is restored.
#
# ⚠️ This file runs both on STAGING and on DEV env ⚠️
#
# 🪄 Based on https://github.com/TurboLabIt/webstackup/blob/master/my-app-template/config/custom/staging/db-post-restore.sql

#UPDATE `core_config_data` SET `value` = 'https://next.turbolab.it/' WHERE `path` IN('web/unsecure/base_url', 'web/secure/base_url');
#UPDATE `wp_options` SET `option_value` = 'https://next.turbolab.it/' WHERE `option_name` IN('siteurl', 'home');
