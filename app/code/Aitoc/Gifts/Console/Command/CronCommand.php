<?php
/**
 * @author Aitoc Team
 * @copyright Copyright (c) 2020 Aitoc (https://www.aitoc.com)
 * @package Aitoc_Gifts
 */


namespace Aitoc\Gifts\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommand extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var \Aitoc\FollowUp\Model\Executer
     */
    private $executer;

    /**
     * CronCommand constructor.
     *
     * @param State                                $state
     * @param \Aitoc\Gifts\Cron\CouponClearExecuter $executer
     */
    public function __construct(
        State $state,
        \Aitoc\Gifts\Cron\CouponClearExecuter $executer
    ) {
        $this->state = $state;
        $this->executer = $executer;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('aitoc:gifts:coupon_clear')
            ->setDescription('Run cron coupon clear')
            ->setDefinition([]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('frontend');

        $output->write('Start....');
        $this->executer->execute();
        $output->writeln('done');
        $output->write('Success....');
    }
}
