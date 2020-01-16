<?php

namespace DoctrineModule\Controller;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Laminas\Mvc\Console\View\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use DoctrineModule\Component\Console\Input\RequestInput;
use Exception;

/**
 * Index controller
 *
 * @license MIT
 * @author Aleksandr Sandrovskiy <a.sandrovsky@gmail.com>
 */
class CliController extends AbstractActionController
{
    /**
     * @var Application
     */
    protected $cliApplication;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Application $cliApplication
     */
    public function __construct(Application $cliApplication)
    {
        $this->cliApplication = $cliApplication;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }


    /**
     * @return ViewModel
     * @throws Exception
     */
    public function cliAction()
    {
        $exitCode = $this->cliApplication->run(new RequestInput($this->getRequest()), $this->output);

        if (is_numeric($exitCode)) {
            $model = new ViewModel();
            $model->setErrorLevel($exitCode);

            return $model;
        }
    }
}
