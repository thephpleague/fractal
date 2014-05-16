<?php

namespace League\Fractal;

class FieldFilterTransformer extends NestedTransformerAbstract
{
    /**
     * Requested fields
     * 
     * @var array
     */
    protected $fields = array();

    /**
     * Allowed fields
     * 
     * @var array
     */
    protected $allowedFields = array();

    /**
     * Partial names to include
     * 
     * @var array
     */
    protected $partials = array();

    /**
     * Mapping of partial names to fields 
     * 
     * @var array
     */
    protected $partialFields = array();

    /**
     * The default partials to include 
     * 
     * @var array
     */
    protected $defaultPartials = array();

    public function transform($data)
    {
        return $this->filter(
            parent::transform($data)
        );
    }

    public function setAllowedFields(array $fields)
    {
        $this->allowedFields = $allowedFields;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function setPartialFields($partial, array $fields)
    {
        if ($this->allowedFields && ($invalidFields = array_diff($fields, $this->allowedFields))) {
            throw new \InvalidArgumentException('The following fields are invalid: '.implode(', ', $invalidFields));
        }

        $this->partialFields[$partial] = $fields;
    }

    public function setPartials(array $partials)
    {
        $this->partials = $partials;
    }

    protected function filter(array $data)
    {
        if (empty($this->partials) && empty($this->fields)) {
            return $data;
        }

        $whitelistFields = array_fill_keys($this->fields, true) ?: array();

        if ($this->partials) {
            foreach ($this->partials as $partial) {
                foreach ($this->getFieldsForPartial($partial) as $field) {
                    $whitelistFields[$field] = true;
                }
            }
        }

        $filtered = array();

        foreach ($data as $field => $value) {
            if (!isset($whitelistFields[$field])) {
                continue;
            }

            $filtered[$field] = $value;
        }

        return $filtered;
    }

    protected function getFieldsForPartial($partial)
    {
        if (!isset($this->partialFields[$partial])) {
            throw new \InvalidArgumentException(
                'Invalid partial name: '.$partial.'. Valid partials: '.implode(', ', array_keys($this->partialFields))
            );
        }

        return $this->partialFields[$partial];
    }

}
