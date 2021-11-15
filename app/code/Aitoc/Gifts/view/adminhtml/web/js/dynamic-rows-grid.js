/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows-grid'
], function (_, dynamicRows) {
    'use strict';

    return dynamicRows.extend({
        defaults: {
            dataProvider: '',
            insertData: [],
            map: null,
            cacheGridData: [],
            deleteProperty: false,
            positionProvider: 'position',
            dataLength: 0,
            identificationProperty: 'id',
            identificationDRProperty: 'id',
            listens: {
                'insertData': 'processingInsertData',
                'recordData': 'initElements setToInsertData'
            },
            mappingSettings: {
                enabled: true,
                distinct: true
            }
        },

        /**
         * Delete record instance
         * update data provider dataScope
         *
         * @param {String|Number} index - record index
         * @param {String|Number} recordId
         */
        deleteRecord: function (index, recordId) {
            var recordInstance,
                lastRecord,
                recordsData,
                lastRecordIndex;

            if (this.deleteProperty) {
                recordsData = this.recordData();
                recordInstance = _.find(this.elems(), function (elem) {
                    return elem.index === index;
                });
                recordInstance.destroy();
                this.elems([]);
                this._updateCollection();
                this.removeMaxPosition();
                recordsData[recordInstance.index][this.deleteProperty] = this.deleteValue;
                this.recordData(recordsData);
                this.reinitRecordData();
                this.reload();
            } else {
                this.update = true;

                if (~~this.currentPage() === this.pages()) {
                    lastRecordIndex = this.startIndex + this.getChildItems().length - 1;
                    lastRecord =
                        _.findWhere(this.elems(), {
                            index: lastRecordIndex
                        }) ||
                        _.findWhere(this.elems(), {
                            index: lastRecordIndex.toString()
                        });

                    lastRecord.destroy();
                }

                this.removeMaxPosition();
                recordsData = this._getDataByProp(recordId);
                this._updateData(recordsData);
                this.update = false;
            }

            this._reducePages();
            this._sort();
        },
    });
});
