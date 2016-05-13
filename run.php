<?php

abstract class Operation {
    protected $result;

    abstract function doSomething($data);

    public function getResult(){
        return $this->result;
    }

}

class OperationSort extends Operation {
    
    public function doSomething($data)
    {
        $a = explode(' ', $data);
        krsort($a);
        $this->result = implode(' ', $a);
        return $this;
    }
}

class OperationReverse extends Operation {
    
    public function doSomething($data)
    {
        $this->result = strrev($data);
        return $this;
    }

}
class OperationRepeat extends Operation {

    public function doSomething($data)
    {
        $charList = $data[0];
        $this->result = str_word_count($data,0,$charList);
        return $this;
    }

}


class CommandData {
    private $operation;
    private $data;

    /**
     * OperationCommand constructor.
     * @param $operation
     * @param $data
     */
    public function __construct($operation, $data)
    {
        $this->operation = $operation;
        $this->data = $data;
    }


    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }    
    
}

class CommandRunner {
    
    protected $operationsCommand = array();
    protected $operation = array();

    /**
     * OperationRunner constructor.
     */
    public function __construct()
    {
        $this->operation['SORT'] = new OperationSort();
        $this->operation['REVERSE'] = new OperationReverse();
        $this->operation['REPEAT'] = new OperationRepeat();
    }


    /**
     *
     * @return array
     */
    protected function getOperationsCommand()
    {
        return $this->operationsCommand;
    }

    /**
     * @param array $operationsCommand
     */
    protected function setOperationsCommand(array $operationsCommand)
    {
        foreach ($operationsCommand as $operationCommand){
            $operation = isset($operationCommand['operation']) ? $operationCommand['operation'] : null;
            $data = isset($operationCommand['data']) ? $operationCommand['data'] : null;
            $this->operationsCommand[] = new CommandData($operation, $data);
        }
        
    }

    /**
     * Załaduj plik do przetworzenia
     *
     * @param $file
     * @return $this
     */
    public function loadFile($file)
    {
        try {
            $handler = new SplFileObject($file, 'r');
        } catch (RuntimeException $e) {
            printf('Error opening file: %s', $e->getMessage());
        }

        $data = array();
        $counter = 0;
        while ($handler->valid()) {
            if( ($handler->key() + 1) % 2 === 0) {
                $data[$counter++]['data'] = trim($handler->current());
            } else {
                $data[$counter]['operation'] = trim($handler->current());
            }

            $handler->next();
        }

        $this->setOperationsCommand($data);
        return $this;
    }

    public function execute()
    {
        foreach ($this->getOperationsCommand() as $command) {
            /** @var CommandData $command */
            if (array_key_exists($command->getOperation(), $this->operation)) {
                printf("-----------------\nOperation: %s\nInput data: %s\nOutput data: %s\n",
                $command->getOperation(),
                $command->getData(),
                $this->operation[$command->getOperation()]
                    ->doSomething($command->getData())
                    ->getResult()
                );
            } else {
                throw new Exception('Operation: ' . $command->getOperation() . ' is not implement yet!');
            }
        }
    }
}

$file = 'data.txt';

try {
    $commandRunner = new CommandRunner();
    $commandRunner
        ->loadFile($file)
        ->execute();
} catch (Exception $e) {
    echo $e->getMessage();
}

