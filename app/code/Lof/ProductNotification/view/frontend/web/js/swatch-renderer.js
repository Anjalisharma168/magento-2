define([
    'jquery'
], function ($) {
    'use strict'

    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {
            /**
             * @private
             */
            _create: function () {
                this._super()
                this.stockForm = $('#pnf-stock')
            },

            /**
             * Rewind options for controls
             *
             * @private
             */
            _Rewind: function (controls) {
                controls.find('div[data-option-id], option[data-option-id]')
                        .removeClass('disabled')
                        .removeAttr('disabled')
                controls.find('div[data-option-empty], option[data-option-empty]').addClass('pnf-disabled')
            },

            _Rebuild: function () {

                var $widget = this,
                    controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                    selected = controls.filter('[data-option-selected]')

                // Enable all options
                $widget._Rewind(controls)

                // done if nothing selected
                if (selected.size() <= 0) {
                    return
                }

                // Disable not available options
                controls.each(function () {
                    var $this = $(this),
                        id = $this.data('attribute-id'),
                        products = $widget._CalcProducts(id)

                    if (selected.size() === 1 && selected.first().data('attribute-id') === id) {
                        return
                    }

                    $this.find('[data-option-id]').each(function () {
                        var $element = $(this),
                            option = $element.attr('data-option-id')

                        if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                            $element.hasClass('selected') ||
                            $element.is(':selected')) {
                            return
                        }

                        if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                            //$element.attr('disabled', true).addClass('disabled');
                        }
                    })
                })
            },

            /**
             * Render controls
             *
             * @private
             */
            _RenderControls: function () {
                var $widget = this,
                    container = this.element,
                    classes = this.options.classes,
                    chooseText = this.options.jsonConfig.chooseText

                $widget.optionsMap = {}

                $.each(this.options.jsonConfig.attributes, function () {
                    var item = this,
                        // options = $widget._RenderSwatchOptions(item),
                        select = $widget._RenderSwatchSelect(item, chooseText),
                        input = $widget._RenderFormInput(item),
                        label = ''

                    var controlLabelId = 'data-option-label-' + item.code + '-' + item.id
                    var options = $widget._RenderSwatchOptions(item, controlLabelId, true)

                    // Show only swatch controls
                    if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                        return
                    }

                    if ($widget.options.enableControlLabel) {
                        label +=
                            '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                            '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>'
                    }

                    $widget.stockForm.append(input)
                    if ($widget.inProductList) {
                        $widget.productForm.append(input)
                        input = ''
                    }

                    // Create new control
                    container.append(
                        '<div class="' + classes.attributeClass + ' ' + item.code +
                        '" data-attribute-code="' + item.code +
                        '" data-attribute-id="' + item.id + '">' +
                        label +
                        '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                        options + select +
                        '</div>' + input +
                        '</div>'
                    )

                    $widget.optionsMap[item.id] = {}

                    // Aggregate options array to hash (key => value)
                    $.each(item.options, function () {
                        if (this.products.length > 0) {
                            $widget.optionsMap[item.id][this.id] = {
                                price: parseInt(
                                    $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                    10
                                ),
                                products: this.products
                            }
                        }
                    })
                })

                // Connect Tooltip
                container
                    .find(
                        '[data-option-type="1"], [data-option-type="2"], [data-option-type="0"], [data-option-type="3"]')
                    .SwatchRendererTooltip()

                // Hide all elements below more button
                $('.' + classes.moreButton).nextAll().hide()

                // Handle events like click or change
                $widget._EventListener()

                // Rewind options
                $widget._Rewind(container)

                //Emulate click on all swatches from Request
                $widget._EmulateSelected($.parseQuery())
                $widget._EmulateSelected($widget._getSelectedAttributes())
            },

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                    attributeId = $parent.data('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput)

                var $stockFormInput = $parent.find('.' + $widget.options.classes.attributeInput)

                $stockFormInput = $widget.stockForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                )

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    )
                }

                if ($this.hasClass('disabled')) {
                    return
                }

                var showForm = false

                if ($this.hasClass('selected')) {
                    $parent.removeAttr('data-option-selected').find('.selected').removeClass('selected')
                    $input.val('')
                    $label.text('')
                    $stockFormInput.val('')
                    showForm = false
                } else {
                    showForm = true
                    $parent.attr('data-option-selected', $this.attr('data-option-id'))
                           .find('.selected')
                           .removeClass('selected')
                    $label.text($this.attr('data-option-label'))
                    $input.val($this.attr('data-option-id'))
                    $stockFormInput.val($this.attr('data-option-id'))
                    $this.addClass('selected')
                }

                $widget._Rebuild()

                var optionId = $this.attr('data-option-id')
                var products = $widget._CalcProducts(optionId)
                var $parent = $widget.element.parents('#maincontent')
                var $addToCartButton = $parent.find('.primary.tocart').eq(0)
                if (showForm) {
                    if ($this.hasClass('pnf-disabled') ||
                        _.intersection(products, $widget.optionsMap[attributeId][optionId].products).length <= 0) {
                        $parent.find('.pnf-form.pnf-stock').show()
                        $addToCartButton.addClass('disabled')
                        $('.pnf-stock > .action').trigger('click')
                    } else {
                        $parent.find('.pnf-form.pnf-stock').hide()
                        $addToCartButton.removeClass('disabled')
                    }
                }

                if ($widget.element.parents($widget.options.selectorProduct)
                           .find(this.options.selectorProductPrice)
                           .is(':data(mage-priceBox)')) {
                    $widget._UpdatePrice()
                }

                // $widget._LoadProductMedia();
                $widget._loadMedia()
                $stockFormInput.trigger('change')
                $input.trigger('change')
            },

            /**
             * Event for select
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnChange: function ($this, $widget) {
                var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                    attributeId = $parent.data('attribute-id'),
                    $input = $parent.find('.' + $widget.options.classes.attributeInput)

                var $stockFormInput = $parent.find('.' + $widget.options.classes.attributeInput)

                $stockFormInput = $widget.stockForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                )

                if ($widget.inProductList) {
                    $input = $widget.productForm.find(
                        '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                    )
                }

                if ($this.val() > 0) {
                    $parent.attr('data-option-selected', $this.val())
                    $input.val($this.val())
                    $stockFormInput.val($this.val())
                } else {
                    $parent.removeAttr('data-option-selected')
                    $input.val('')
                    $stockFormInput.val('')
                }

                $widget._Rebuild()
                $widget._UpdatePrice()
                $widget._LoadProductMedia()
                $input.trigger('change')
                $stockFormInput.trigger('change')
            },

        })

        return $.mage.SwatchRenderer
    }
})
