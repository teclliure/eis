<?php
namespace Teclliure\InvoiceBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;
use Teclliure\CustomerBundle\Entity\Customer;

class CustomerToNumberTransformer implements DataTransformerInterface
{
    /**
    * @var ObjectManager
    */
    private $om;

    /**
    * @param ObjectManager $om
    */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
    * Transforms an object (customer) to a string (number).
    *
    * @param  Customer|null $customer
    * @return string
    */
    public function transform($customer)
    {
        if (null === $customer) {
            return "";
        }

        return $customer->getId();
    }

    /**
    * Transforms a string (number) to an object (customer).
    *
    * @param  string $number
    *
    * @return Customer|null
    *
    * @throws TransformationFailedException if object (customer) is not found.
    */
    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }

        $customer = $this->om
        ->getRepository('TeclliureCustomerBundle:Customer')
        ->findOneBy(array('id' => $number));

        if (null === $customer) {
            throw new TransformationFailedException(sprintf(
            'An customer with number "%s" does not exist!',
            $number
            ));
        }

        return $customer;
    }
}