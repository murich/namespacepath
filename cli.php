<?php
namespace Murich\Namespacepath;

class Controller
{
    /**
     * @var array
     */
    protected $namespaceByFile = [];

    /**
     * @var
     */
    protected $baseNamespace;

    /**
     * @var
     */
    protected $basePath;

    /**
     * @param $text
     * @return string
     */
    protected function input($text) {
        return trim(readline($text . "\n"));
    }

    /**
     * @param $text
     */
    protected function output($text)
    {
        fwrite(STDOUT, $text . "\n");
    }

    /**
     * @param $fileNamespace
     * @return mixed
     */
    protected function getRelativePath($fileNamespace)
    {
        return str_replace([$this->baseNamespace, '\\'], ['', '/'], $fileNamespace);
    }

    /**
     * @param $relativePath
     * @return string
     */
    protected function provideDirectory($relativePath)
    {
        $folders = explode('/', $relativePath);
        $folderPath = $this->basePath;

        foreach($folders as $folder) {
            $folderPath .= '/' . $folder;
            if (!is_dir($folderPath)) {
                mkdir($folderPath);
            }
        }

        return $folderPath;
    }

    /**
     * @param $file
     */
    protected function extractNamespaceFromFile($file)
    {
        $fileContent = file_get_contents($file);
        preg_match('/namespace (.*?);/', $fileContent, $matches);
        if (isset($matches[1])) {
            $this->namespaceByFile[$file] =  trim($matches[1], '\\');
        }
    }

    /**
     * @return string
     */
    protected function renderNamespacesInfo()
    {
        $this->output(print_r(array_values(array_unique($this->namespaceByFile)), true));
    }

    /**
     * Runs the process
     */
    public function action()
    {
        $this->output('Welcome. This tool will help to change path of your classess according to PSR-4');
        $this->basePath = $this->input('Please tell me directory of your PHP classes');
        foreach(glob($this->basePath . '/*') as $file) {
            $this->extractNamespaceFromFile($file);
        }

        $this->renderNamespacesInfo();
        $this->baseNamespace = $this->input('Please provide base namespace');

        foreach($this->namespaceByFile as $file => $namespace) {
            $relativePath = $this->getRelativePath($namespace);
            $destination = $this->provideDirectory($relativePath);
            rename($file, $destination . '/' . basename($file));
        }
    }
}

(new Controller())->action();