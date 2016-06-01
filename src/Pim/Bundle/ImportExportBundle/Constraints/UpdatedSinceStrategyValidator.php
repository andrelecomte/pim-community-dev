<?php

namespace Pim\Bundle\ImportExportBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for UpdateSinceStrategy constraint
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedSinceStrategyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UpdatedSinceStrategy) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UpdateSinceStrategy');
        }

        if (empty($value) &&
            'since_date' === $constraint->jobInstance->getRawConfiguration()['updated_since_strategy']) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
