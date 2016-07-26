/**
 * CoreShop
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2016 Dominik Pfaffenbauer (http://www.pfaffenbauer.at)
 * @license    http://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

pimcore.registerNS('pimcore.plugin.coreshop.pricerules.conditions.products');

pimcore.plugin.coreshop.pricerules.conditions.products = Class.create(pimcore.plugin.coreshop.pricerules.conditions.abstract, {

    type : 'products',

    products : null,

    getForm : function () {
        this.products = new pimcore.plugin.coreshop.object.objectMultihref(this.data ? this.data.products : [], {
                classes: [
                     {classes: coreshop.settings.classMapping.product}
                ],
                name: "products",
                title: "",
                height: 200,
                width: 500,
                columns: [],

                columnType: null,
                datatype: "data",
                fieldtype: "objects"
            });

        this.form = new Ext.form.FieldSet({
            items : [
                this.products.getLayoutEdit()
            ]
        });

        return this.form;
    },

    getValues : function() {
        return {
            products : this.products.getValue()
        };
    }
});
