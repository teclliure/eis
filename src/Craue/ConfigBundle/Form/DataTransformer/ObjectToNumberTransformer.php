<?php
namespace Craue\ConfigBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class ObjectToNumberTransformer implements DataTransformerInterface
{
    /**
    * @var ObjectManager
    */
    private $om;

    /**
     * @var string
     */
    private $string;

    /**
    * @param ObjectManager $om
    */
    public function __construct(ObjectManager $om, $class)
    {
        $this->om = $om;
        $this->class = $class;
    }

    /**
    * Transforms an object (object) to a string (number).
    *
    * @param  Object|null $object
    * @return string
    */
    public function reverseTransform($object)
    {
        if (null === $object) {
            return "";
        }


        return $object->getId();
    }

    /**
    * Transforms a string (number) to an object (object).
    *
    * @param  string $number
    *
    * @return Object|null
    *
    * @throws TransformationFailedException if object (object) is not found.
    */
    public function transform($number)
    {
        if (!$number) {
            return null;
        }

        $object = $this->om
        ->getRepository($this->class)
        ->findOneBy(array('id' => $number));

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
            'An object with number "%s" does not exist!',
            $number
            ));
        }

        return $object;
    }
}