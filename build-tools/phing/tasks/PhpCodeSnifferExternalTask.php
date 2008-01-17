<?php

include_once 'phing/Task.php';

class PhpCodeSnifferExternalTask extends Task
{
    private $programpath = 'phpcs';
    private $report = 'full';
    private $standard = 'PEAR';
    private $targetdir = false;
    private $filesets = array();
    private $todir = false;
    private $outfile = 'phpcs_results.txt';
    private $recursion = true;
    private $tabwidth = false;
    private $extensions = false;
    private $showwarnings = true;

    public function setReport($report)
    {
        $this->report = $report;
    }

    public function setStandard($standard)
    {
        $this->standard = $standard;
    }

    public function setTodir($todir)
    {
        $this->todir = $todir;
    }

    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    public function setRecursion($recursion)
    {
        $this->recursion = $recursion;
    }

    public function setTabwidth($tabwidth)
    {
        $this->tabwidth = $tabwidth;
    }

    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    public function setShowwarnings($showwarnings)
    {
        $this->showwarnings = $showwarnings;
    }

    public function setTargetdir($targetdir)
    {
        $this->targetdir = $targetdir;
    }

	/**
	 * Add a new fileset containing the XML results to aggregate
	 *
	 * @param FileSet the new fileset containing XML results.
	 */
	function addFileSet(FileSet $fileset)
	{
		$this->filesets[] = $fileset;
	}

	/**
	 * Iterate over all filesets and return the filename of all files.
	 *
	 * @return array an array of filenames
	 */
	private function getFilenames()
	{
		$filenames = array();

		foreach ($this->filesets as $fileset)
		{
			$ds = $fileset->getDirectoryScanner($this->project);
			$ds->scan();

			$files = $ds->getIncludedFiles();

			foreach ($files as $file)
			{
				$filenames[] = $ds->getBaseDir() . "/" . $file;
			}
		}

		return $filenames;
	}

	function main()
	{
		$arguments = $this->constructArguments();

		$this->log("Running Php_CodeSniffer...");
		$command = $this->programpath . " " . $arguments;
		$return = 2;
		exec($command, $output, $return);
        switch ($return) {
            case 0:
                $this->log('... Great! No coding standards violations.');
                break;
            case 1:
		        $this->log('... and found coding errors in the parsed files.');
                break;
            case 2:
		        throw new BuildException("'Php_CodeSniffer' doesn't seem to be run correctly: Command run was: '$command'");
                break;
		}

	}

	private function constructArguments()
	{
        $arguments = '';
        if ($this->recursion) {
            $arguments.= ' -l';
        }
        if ($this->showwarnings) {
            $arguments.= ' -w';
        } else {
            $arguments.= ' -n';
        }
        if ($this->tabwidth) {
            $arguments.= ' --tab-width=' . $this->tabwidth;
        }
        if ($this->extensions) {
            $arguments.= ' --extensions=' . $this->extensions;
        }

        $arguments.= ' --standard=' . $this->standard;
        $arguments.= ' --report=' . $this->report;
        if($this->targetdir) {
            $arguments.= ' ' . $this->targetdir;
        } else {
            $filenames = $this->getFilenames();
            if(count($filenames) == 0) {
                throw new BuildException("'Php_CodeSniffer' cannot be run, no files given. Set the targetdir attribute or provide a fileset.");
            }
            $arguments.= ' ' . implode(' ', $filenames);
        }
        $arguments.= ' > ' . $this->todir . DIRECTORY_SEPARATOR . $this->outfile;

        return $arguments;
	}

}
