<?php
/**
 * Recipe plugin for Craft CMS
 *
 * Recipe Model
 *
 * @author    nystudio107
 * @copyright Copyright (c) 2016 nystudio107
 * @link      http://nystudio107.com
 * @package   Recipe
 * @since     1.0.0
 */

namespace Craft;

class RecipeModel extends BaseModel
{
    /**
     * @return array
     */
    protected function defineAttributes()
    {
        return array_merge(parent::defineAttributes(), array(
            'name'                  => array(AttributeType::String, 'default' => ''),
            'description'           => array(AttributeType::String, 'default' => ''),
            'serves'                => array(AttributeType::Number, 'default' => 1),
            'ingredients'           => array(AttributeType::Mixed, 'default' => ''),
            'directions'            => array(AttributeType::Mixed, 'default' => ''),
            'imageId'               => array(AttributeType::Number, 'default' => 0),
            'prepTime'              => array(AttributeType::Number),
            'cookTime'              => array(AttributeType::Number),
            'totalTime'             => array(AttributeType::Number),

            'ratings'               => array(AttributeType::Mixed),

            'servingSize'           => array(AttributeType::String, 'default' => ''),
            'calories'              => array(AttributeType::Number),
            'carbohydrateContent'   => array(AttributeType::Number),
            'cholesterolContent'    => array(AttributeType::Number),
            'fatContent'            => array(AttributeType::Number),
            'fiberContent'          => array(AttributeType::Number),
            'proteinContent'        => array(AttributeType::Number),
            'saturatedFatContent'   => array(AttributeType::Number),
            'sodiumContent'         => array(AttributeType::Number),
            'sugarContent'          => array(AttributeType::Number),
            'transFatContent'       => array(AttributeType::Number),
            'unsaturatedFatContent' => array(AttributeType::Number),
        ));
    }

/* -- Accessors ------------------------------------------------------------ */

    /**
     * @return string the URL to the image
     */
    public function getImageUrl()
    {
        $result = "";
        if (isset($this->imageId))
        {
            $image = craft()->assets->getFileById($this->imageId);
            if ($image)
                $result = $image->url;
        }
        return $result;
    }

    /**
     * @return array of strings for the ingedients
     */
    public function getIngredients($outputUnits="imperial", $serving=0)
    {
        $result = array();
        foreach ($this->ingredients as $row)
        {
            $convertedUnits = "";
            $ingredient = "";
            if ($row['quantity'])
            {

/* -- Multiply the quanity by how many servings we want */

                $multiplier = 1;
                if ($serving > 0)
                    $multiplier = $serving / $this->serves;
                $quantity = $row['quantity'] * $multiplier;
                $originalQuantity = $quantity;

/* -- Do the units conversion */

                if ($outputUnits == 'imperial')
                {
                    if ($row['units'] == "mls")
                    {
                        $convertedUnits = "tsps";
                        $quantity = $quantity * 0.2;
                    }

                    if ($row['units'] == "ls")
                    {
                        $convertedUnits = "cups";
                        $quantity = $quantity * 4.2;
                    }

                    if ($row['units'] == "mgs")
                    {
                        $convertedUnits = "ozs";
                        $quantity = $quantity * 0.000035274;
                    }

                    if ($row['units'] == "gs")
                    {
                        $convertedUnits = "ozs";
                        $quantity = $quantity * 0.035274;
                    }
                }

                if ($outputUnits == 'metric')
                {
                    if ($row['units'] == "tsps")
                    {
                        $convertedUnits = "mls";
                        $quantity = $quantity * 4.929;
                    }

                    if ($row['units'] == "tbsps")
                    {
                        $convertedUnits = "mls";
                        $quantity = $quantity * 14.787;
                    }

                    if ($row['units'] == "flozs")
                    {
                        $convertedUnits = "mls";
                        $quantity = $quantity * 29.574;
                    }

                    if ($row['units'] == "cups")
                    {
                        $convertedUnits = "ls";
                        $quantity = $quantity * 0.236588;
                    }

                    if ($row['units'] == "ozs")
                    {
                        $convertedUnits = "gs";
                        $quantity = $quantity * 28.3495;
                    }

                    $quantity = round($quantity, 1);
                }

/* -- Convert imperial units to nice fractions */

                if ($outputUnits == 'imperial')
                    $quantity = $this->convertToFractions($quantity);
                $ingredient .= $quantity;
            }
            if ($row['units'])
            {
                $units = $row['units'];
                if ($convertedUnits)
                    $units = $convertedUnits;
                if ($originalQuantity <= 1)
                {
                    $units = rtrim($units);
                    $units = rtrim($units, 's');
                }
                $ingredient .= " " . $units;
            }
            if ($row['ingredient'])
                $ingredient .= " " . $row['ingredient'];
            array_push($result, TemplateHelper::getRaw($ingredient));
        }
        return $result;
    }

    /**
     * @return array of strings for the directions
     */
    public function getDirections()
    {
        $result = array();
        foreach ($this->directions as $row)
        {
            $direction = $row['direction'];
            array_push($result, TemplateHelper::getRaw($direction));
        }
        return $result;
    }

    /**
     * @return string the aggregate rating for this recipe
     */
    public function getAggregateRating()
    {
        $result = 0;
        $total = 0;
        if (isset($this->ratings) && !empty($this->ratings))
        {
            foreach ($this->ratings as $row)
            {
                $result += $row['rating'];
                $total++;
            }
            $result = $result / $total;
        }
        else
            $result = "";
        return $result;
    }

    /**
     * @return string the fractionalized string
     */
    private function convertToFractions($quantity)
    {
        $result = "";
        $whole = floor($quantity);
        $fraction = $quantity - $whole;
        switch ($fraction)
        {
            case 0:
                $fraction = "";
            break;

            case 0.25:
                $fraction = " &frac14;";
            break;

            case 0.5:
                $fraction = " &frac12;";
            break;

            case 0.75:
                $fraction = " &frac34;";
            break;

            case 0.125:
                $fraction = " &#x215B;";
            break;

            case 0.375:
                $fraction = " &#x215C;";
            break;

            case 0.625:
                $fraction = " &#x215D;";
            break;

            case 0.875:
                $fraction = " &#x215E;";
            break;

            default:
                $precision = 5;
                $pnum = round($fraction, $precision);
                $denominator = pow(10, $precision);
                $numerator = $pnum * $denominator;
                $fraction = "<sup>" . $numerator . "</sup>&frasl;<sub>" . $denominator . "</sub>";
            break;
        }
        if ($whole == 0)
            $whole = "";
        $result = $whole . $fraction;
        return $result;
    }
}