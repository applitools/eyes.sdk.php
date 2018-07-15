<?php
/**
 * Created by PhpStorm.
 * User: USER
 * Date: 05/04/2017
 * Time: 18:03
 */

namespace Applitools;


class FixedScaleProviderFactory extends ScaleProviderFactory
{
    private $scaleRatio;

    /**
     * FixedScaleProviderFactory constructor.
     * @param float $scaleRatio
     * @param PropertyHandler $scaleProviderHandler
     */
    public function __construct($scaleRatio, PropertyHandler $scaleProviderHandler) {
        parent::__construct($scaleProviderHandler);
        $this->scaleRatio = $scaleRatio;
    }

    /**
     * @inheritdoc
     */
    protected function getScaleProviderImpl($imageToScaleWidth)
    {
        return new FixedScaleProvider($this->scaleRatio);
    }
}