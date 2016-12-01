<?php
/**
 * FPDI plugin plugin for Craft CMS
 *
 * FpdiPlugin_Pdf Model
 *
 * --snip--
 * Models are containers for data. Just about every time information is passed between services, controllers, and
 * templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 * --snip--
 *
 * @author    @cole007
 * @copyright Copyright (c) 2016 @cole007
 * @link      http://ournameismud.co.uk/
 * @package   FpdiPlugin
 * @since     1.0.0
 */

namespace Craft;

class FpdiPlugin_PdfModel extends BaseModel
{
    /**
     * Defines this model's attributes.
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'someField'     => array(AttributeType::String, 'default' => 'some value'),
        ));
    }

}