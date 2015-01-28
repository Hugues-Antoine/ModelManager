<?php
/*
 * This file is part of Pomm's ModelManager package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\ModelManager\Generator;

use PommProject\Foundation\Session\Session;
use PommProject\Foundation\ParameterHolder;

use PommProject\ModelManager\Exception\GeneratorException;
/**
 * BaseGenerator
 *
 * Base class for Generator
 *
 * @package ModelManager
 * @copyright 2014 Grégoire HUBERT
 * @author Grégoire HUBERT
 * @license X11 {@link http://opensource.org/licenses/mit-license.php}
 * @abstract
 */
abstract class BaseGenerator
{
    private $session;

    protected $schema;
    protected $relation;
    protected $filename;
    protected $namespace;
    protected $flexibe_container;

    /*
     * __construct
     *
     * Constructor
     *
     * @access public
     * @param  Session $session
     * @param  string  $relation
     * @param  string  $filename
     * @param  string  $namespace
     * @return void
     */
    public function __construct(Session $session, $schema, $relation, $filename, $namespace, $flexible_container = null)
    {
        $this->session   = $session;
        $this->schema    = $schema;
        $this->relation  = $relation;
        $this->filename  = $filename;
        $this->namespace = $namespace;
        $this->flexibe_container = $flexible_container;
    }

    /**
     * outputFileCreation
     *
     * Output what the generator will do.
     *
     * @access protected
     * @param  array            $output
     * @return BaseGenerator    $this
     */
    protected function outputFileCreation(array &$output = [])
    {
        if (file_exists($this->filename)) {
            $output[] = ['status' => 'ok', 'operation' => 'overwriting', 'file' => $this->filename];
            /*
                sprintf(
                    " <fg=green>✓</fg=green>  <fg=cyan>Overwriting</fg=cyan> file <fg=yellow>'%s'</fg=yellow>.",
                    $this->filename
                )
            );
             */
        } else {
            $output[] = ['status' => 'ok', 'operation' => 'creating', 'file' => $this->filename];
            /*
            $output->writeln(
                sprintf(
                    " <fg=green>✓</fg=green>  <fg=green>Creating</fg=green> file <fg=yellow>'%s'</fg=yellow>.",
                    $this->filename
                )
            );
             */
        }

        return $output;
    }

    /**
     * setSession
     *
     * Set the session.
     *
     * @access protected
     * @param  Session       $session
     * @return BaseGenerator $this
     */
    protected function setSession(Session $session)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * getSession
     *
     * Return the session is set. Throw an exception otherwise.
     *
     * @access protected
     * @throw  GeneratorException
     * @return Session
     */
    protected function getSession()
    {
        if ($this->session === null) {
            throw new GeneratorException(sprintf("Session is not set."));
        }

        return $this->session;
    }

    /**
     * getInspector
     *
     * Shortcut to session's inspector client.
     *
     * @access protected
     * @return Inspector
     */
    protected function getInspector()
    {
        return $this->getSession()->getClientUsingPooler('inspector', null);
    }

    /**
     * generate
     *
     * Called to generate the file.
     *
     * @access public
     * @param  ParameterHolder  $input
     * @return array            $output
     */
    abstract public function generate(ParameterHolder $input, array $output = []);

    /**
     * getCodeTemplate
     *
     * Return the code template for files to be generated.
     *
     * @access protected
     * @return string
     */
    abstract protected function getCodeTemplate();

    /**
     * mergeTemplate
     *
     * Merge templates with given values.
     *
     * @access protected
     * @param  array  $variables
     * @return string
     */
    protected function mergeTemplate(array $variables)
    {
        $prepared_variables = [];
        foreach ($variables as $name => $value) {
            $prepared_variables[sprintf("{:%s:}", $name)] = $value;
        }

        return strtr(
            $this->getCodeTemplate(),
            $prepared_variables
        );
    }

    /**
     * saveFile
     *
     * Write the genreated content to a file.
     *
     * @access protected
     * @param  string        $filename
     * @param  string        $content
     * @return BaseGenerator $this
     */
    protected function saveFile($filename, $content)
    {
        if (!file_exists(dirname($filename))) {
            if (mkdir(dirname($filename), 0777, true) === false) {
                throw new GeneratorException(
                    sprintf(
                        "Could not create directory '%s'.",
                        dirname($filename)
                    )
                );
            }
        }

        if (file_put_contents($filename, $content) === false) {
            throw new GeneratorException(
                sprintf(
                    "Could not open '%s' for writing.",
                    $filename
                )
            );
        }

        return $this;
    }

    /**
     * checkOverwrite
     *
     * Check if the file exists and if it the write is forced.
     *
     * @access protected
     * @param  ParameterHolder  $input
     * @return BaseGenerator    $this
     */
    protected function checkOverwrite(ParameterHolder $input)
    {
        if (file_exists($this->filename) && !$input->getParameter('force', false)) {
            throw new GeneratorException(sprintf("Cannot overwrite file '%s' without --force option.", $this->filename));
        }

        return $this;
    }
}
