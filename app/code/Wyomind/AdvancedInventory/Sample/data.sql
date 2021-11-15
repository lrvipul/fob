/* sample data file used for the Wyomind's demo store: https://demo2.wyomind.com */

SET FOREIGN_KEY_CHECKS=0;

TRUNCATE TABLE {{table:advancedinventory_stock}};
TRUNCATE TABLE `{{table:advancedinventory_item}}`;

INSERT INTO `{{table:advancedinventory_item}}` (`id`, `product_id`, `multistock_enabled`)
SELECT null, entity_id, 1 from {{table:catalog_product_entity}} where type_id != 'configurable';


INSERT INTO `{{table:advancedinventory_stock}}` (`id`, `product_id`, `item_id`, `place_id`, `manage_stock`, `quantity_in_stock`, `backorder_allowed`, `use_config_setting_for_backorders`)
SELECT null, ai.product_id, ai.id, pos.place_id, 1, FLOOR(0 + (RAND() * 30)), FLOOR(0 + (RAND() * 3)), 0 from {{table:advancedinventory_item}} as ai, {{table:pointofsale}} as pos;

