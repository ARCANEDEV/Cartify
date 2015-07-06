<?php namespace Arcanedev\Cartify\Traits;

trait CheckerTrait
{
    /* ------------------------------------------------------------------------------------------------
     |  Validation Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Check if it is valid string
     *
     * @param  mixed $value
     *
     * @return bool
     */
    private function isValidString($value)
    {
        return ! (
            $this->checkIsNullOrEmpty($value) ||
            $this->checkIsEmptyString($value)
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Check Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Check the value is not empty
     *
     * @param  mixed $value
     *
     * @return bool
     */
    private function checkIsNullOrEmpty($value)
    {
        return is_null($value) || empty($value);
    }

    /**
     * Check is a string value
     *
     * @param  string $value
     *
     * @return bool
     */
    private function checkIsEmptyString($value)
    {
        return is_string($value) && trim($value) === '';
    }

    /**
     * Check is a double value
     *
     * @param  mixed $value
     *
     * @return bool
     */
    private function checkIsDoubleNumber($value)
    {
        return is_numeric($value) || is_double($value);
    }

    /**
     * Check is an integer value
     *
     * @param  double $value
     *
     * @return bool
     */
    private function checkIsIntegerNumber($value)
    {
        return is_numeric($value) || is_int($value);
    }
}
