<?php
/*
*MIT License
*
*Copyright (c) 2018 Christophe Brachet
*
*Permission is hereby granted, free of charge, to any person obtaining a copy
*of this software and associated documentation files (the "Software"), to deal
*in the Software without restriction, including without limitation the rights
*to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
*copies of the Software, and to permit persons to whom the Software is
*furnished to do so, subject to the following conditions:
*
*The above copyright notice and this permission notice shall be included in all
*copies or substantial portions of the Software.
*
*THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
*IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
*FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
*AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
*LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
*OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
*SOFTWARE.
*
*/
declare(strict_types=1);
namespace Aspi\Framework\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Pimple\Container;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\Table;



class InitAppCommand extends Command
{
    private $container = null;
    private $title = 'ASPI - Command to init an application';
    public function __construct(Container $container)
    {
        parent::__construct();
        $this->container = $container;
    }
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('aspi:init')

        // the short description shown while running "php bin/console list"
        ->setDescription($this->title)

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows to init an application');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
            $io = new SymfonyStyle($input, $output);
            $extensions  = get_loaded_extensions();
            if(!in_array('PDO',$extensions) && !in_array('pdo_mysql', $extensions))
            {
                throw new \RuntimeException(
                    'You need to install php pdo_mysql extension (http://php.net/manual/fr/pdo.installation.php).'
                );
            }
            $io->title($this->title);
            $helper = $this->getHelper('question');
            $question = new Question('Please enter database name : ', 'DBName');
            $question->setValidator(function ($answer) {
                if (!preg_match("/^[a-z \s]+$/", $answer)) {
                    throw new \RuntimeException(
                        'Database name must be a string(not empty).'
                    );
                }
                
            
                return $answer;
            });
            $dbName = $helper->ask($input, $output, $question);
            $question = new Question('What is the database root password? ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(function ($password) {
            // the connection configuration (root user)
            if($password == null) $password ='';
            try
            {
                //retrieve root database connexion from password
                $this->container['SchemaManager']->connectRootUser($password);
            }
            catch (ConnectionException $e) {
                       
                if($e->getErrorCode() == 1045)
                {
                    throw new \RuntimeException(
                        'Can \'t connect : Bad root password'
                    );
                }
                else if($e->getErrorCode() == 2002)
                {
                            throw new \RuntimeException(
                                'Mysql Database Service not started.Start Mysql server !!!'
                            );
                        }
        
                     }
                   
                });
                $helper->ask($input, $output, $question);
                $question = new Question('Please enter new  User (Database) : ');
                $question->setValidator(function ($answer) {
                    if (!preg_match("/^[a-z \s]+$/", $answer)) {
                        throw new \RuntimeException(
                            'Database name must be a string(not empty).'
                        );
                    }
                    
                
                    return $answer;
                });
                $userFramework = $helper->ask($input, $output, $question);
                $question = new Question('Pleaser enter new  password (Database) : ');
                $question->setHidden(true);
                $question->setHiddenFallback(false);
                $passwordFramework = $helper->ask($input, $output, $question);
                $question = new Question('Please enter new  User (CMS) : ');
                $question->setValidator(function ($answer) {
                    if (!preg_match("/^[a-z \s]+$/", $answer)) {
                        throw new \RuntimeException(
                            'Database name must be a string(not empty).'
                        );
                    }
                    
                
                    return $answer;
                });
                $userAdmin = $helper->ask($input, $output, $question);
                $question = new Question('Please enter a domain for your site : ');
                $domainName = $helper->ask($input, $output, $question);
                $io->newLine();
                if($dbName !=null)
                {
                
                    try
                    {
                        $this->container['SchemaManager']->createDatabase($dbName,$userFramework,$passwordFramework);
                        $tables = $this->container['SchemaManager']->getCreatedTables();
                        $rows = array();
                        $i = 0;
                        foreach($tables as $row)
                        {
                            $rows[] = array($row);
                            if($i < (count($tables)-1))
                            {
                                $rows[] = new TableSeparator();
                            }
                            $i++;
                        }
                        $io->success('Table '.$dbName.' created !');
                        $table = new Table($output);
                        $table->setStyle('box');
                        $table->setHeaders(array('Table '.$dbName))
                              ->setRows($rows);
                        $table->render();
                        $this->container['DatabaseConfig']->writeFile($dbName,$userFramework,$passwordFramework);
                        if($this->container['isCMS'])
                        {
                            $themes = array_values(preg_grep('/^([^.])/', scandir(__DIR__.'/../../../../../../src/CMS/Themes', SCANDIR_SORT_ASCENDING)));
                            $question = new ChoiceQuestion(
                                'Choose a theme',
                                $themes,
                                0
                            );
                            $question->setErrorMessage('Theme %s is invalid.');
                            $this->container['theme'] = $helper->ask($input, $output, $question);
                            $io->section('Creating theme ...');
                            $theme = $this->container['ThemeManager']->createTheme();
                           
                        }
                        $io->section('Copying public files ...');
                        $this->container['FileManager']->copyDirectory();
                        $this->container['Sass']->run(__DIR__.'/../../../../../../src/CMS/Themes/'.$this->container['theme'].'/sass');
                        if($this->container['isCMS'])
                        {
                          
                           $routingTheme =__DIR__.'/../../../../../../src/CMS/Themes/'.$this->container['theme'].'/routing.yaml';
                           $blob = file_get_contents($routingTheme);
                           $hookPath =__DIR__.'/../../../../../../src/CMS/Themes/'.$this->container['theme'].'/src/hook.php';
                           $hook = file_get_contents($hookPath);
                           $this->container['SiteManager']->add($domainName,$blob,$theme,$hook);
                        }
                        else
                        {
                           
                           
                            $routingPath = __DIR__.'/../../../application/Seeding/routing.yml';
                            $blob = file_get_contents($routingPath);
                            $this->container['SiteManager']->add($domainName,$blob);
                        }
                    
                    }
                    catch (PDOException $err) {
                        throw new \RuntimeException(
                            'PDO error  : '. $err->getMessage()
                        );
                    }
                    catch(SchemaException $schex)
                    {
                        throw new \RuntimeException(
                            'Error creating schema database :  '. $schex->getMessage()
                        );
                 
                    }
                    catch(\Exception $ex)
                    {
                        throw new \RuntimeException(
                            'Other error :  '. $ex->getMessage()
                        );
                                                	
                    }
                }
                if($this->container['isCMS'])
                {
                    $io->success('CMS ready to use !');
                }
                else
                {
                    $io->success('Framework ready to use !');
                }
             
    }
}
