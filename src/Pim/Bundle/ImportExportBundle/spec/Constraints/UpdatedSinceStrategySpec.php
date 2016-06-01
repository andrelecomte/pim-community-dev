<?php

namespace spec\Pim\Bundle\ImportExportBundle\Constraints;

use Akeneo\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Constraint;

class UpdatedSinceStrategySpec extends ObjectBehavior
{
    function let(JobInstance $jobInstance)
    {
        $this->beConstructedWith($jobInstance);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\ImportExportBundle\Constraints\UpdatedSinceStrategy');
    }

    function it_is_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_has_options()
    {
        $this->getDefaultOption()->shouldReturn('jobInstance');
    }

    function it_has_targets()
    {
        $this->getTargets()->shouldReturn(Constraint::PROPERTY_CONSTRAINT);
    }
}
