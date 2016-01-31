<?php
namespace Application\Form;

use DoctrineORMModule\Service\MigrationsCommandFactory;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Validator\File\FilesSize;
use Zend\Validator\File\MimeType;

class ProductForm extends Form
{
    /**
     * Ctor
     */
    public function __construct()
    {
        parent::__construct('product');
        $this->setAttribute('method', 'POST');
        $this->addNameInput()
            ->addDescriptionInput()
            ->addImageInput();

    }

    public function init()
    {
        $inputFilter = $this->getInputFilter();
        $input = $inputFilter->get('name');
        $input->setRequired(true);

        $input = $inputFilter->get('description');
        $input->setRequired(true);

        $input = $inputFilter->get('image');

        $validator = new FilesSize([
            'options' => [
                'min' => '20KB',
                'max' => '8MB',
            ]
        ]);
        $input->getValidatorChain()->attach($validator);
        $validator = new MimeType();
        $validator->setMimeType(['image/png', 'image/jpeg']);
        $input->getValidatorChain()->attach($validator);
        $input->setRequired(true);
    }

    /**
     * @return $this
     */
    protected function addNameInput()
    {
        $element = new Element\Text('name');
        $this->add($element);
        return $this;
    }

    protected function addDescriptionInput()
    {
        $element = new Element\Textarea('description');
        $this->add($element);
        return $this;
    }

    protected function addImageInput()
    {
        $element = new Element\File('image');
        $this->add($element);
        return $this;
    }
}