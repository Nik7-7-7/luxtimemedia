<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
abstract class Quform_Element_Container extends Quform_Element
{
    /**
     * The child elements of this container
     * @var Quform_Element[]
     */
    protected $elements = array();

    /**
     * Whether the container contains at least one non-empty child element, this will be processed and set when processing the form
     * @var boolean
     */
    protected $hasNonEmptyChild = false;

    /**
     * Whether the container contains at least one conditionally visible child element, this will be processed and set when processing the form
     * @var boolean
     */
    protected $hasVisibleChild = false;

    /**
     * Get the child elements of this element
     *
     * @return Quform_Element[] The form elements
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Add a form element to the container
     *
     * @param Quform_Element $element
     */
    public function addElement(Quform_Element $element)
    {
        $this->elements[$element->getName()] = $element;
    }

    /**
     * Get the HTML for the title and description
     *
     * @return  string
     */
    protected function getTitleDescriptionHtml()
    {
        $output = '';
        $title = $this->config('title');
        $description = $this->config('description');
        $showTitle = Quform::isNonEmptyString($title);
        $showDescription = Quform::isNonEmptyString($description);

        switch (get_class($this)) {
            case 'Quform_Element_Page':
                $prefix = 'page';
                break;
            case 'Quform_Element_Group':
                $prefix = 'group';
                break;
        }

        if ($showTitle || $showDescription) {
            $output .= sprintf('<div class="quform-%s-title-description">', $prefix);

            if ($showTitle) {
                $output .= Quform::getHtmlTag($this->config('titleTag'), array('class' => sprintf('quform-%s-title', $prefix)), do_shortcode($title));
            }

            if ($showDescription) {
                $output .= Quform::getHtmlTag('p', array('class' => sprintf('quform-%s-description', $prefix)), do_shortcode($description));
            }

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * @return array
     */
    abstract protected function getContainerClasses();

    /**
     * Render the CSS for this container and its children
     *
     * @param   array   $context
     * @return  string
     */
    protected function renderCss(array $context = array())
    {
        $css = '';

        foreach ($this->elements as $element) {
            $css .= $element->getCss($context);
        }

        $css .= parent::renderCss($context);

        return $css;
    }

    /**
     * Set whether the container contains at least one non-empty child element, this will be processed and set when processing the form
     *
     * @param boolean $flag
     */
    public function setHasNonEmptyChild($flag)
    {
        $this->hasNonEmptyChild = (bool) $flag;
    }

    /**
     * Set whether the container contains at least one conditionally visible child element
     *
     * @param boolean $flag
     */
    public function setHasVisibleChild($flag)
    {
        $this->hasVisibleChild = (bool) $flag;
    }

    /**
     * Get whether the container contains at least one conditionally visible child element
     *
     * @return boolean
     */
    public function getHasVisibleChild()
    {
        return $this->hasVisibleChild;
    }

    /**
     * Get whether the container is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return ! $this->hasNonEmptyChild;
    }

    /**
     * Get whether the container is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return $this->isConditionallyHidden() || ! $this->hasVisibleChild;
    }

    /**
     * Get the recursive iterator to iterate over the form elements
     *
     * Modes:
     * RecursiveIteratorIterator::LEAVES_ONLY
     * RecursiveIteratorIterator::SELF_FIRST
     * RecursiveIteratorIterator::CHILD_FIRST
     * RecursiveIteratorIterator::CATCH_GET_CHILD
     *
     * @param  int $mode
     * @return RecursiveIteratorIterator
     */
    public function getRecursiveIterator($mode = RecursiveIteratorIterator::LEAVES_ONLY)
    {
        return new RecursiveIteratorIterator(
            new Quform_Element_Container_Iterator($this),
            $mode
        );
    }
}
