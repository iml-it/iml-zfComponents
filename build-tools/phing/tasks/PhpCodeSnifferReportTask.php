<?php

require_once 'phing/Task.php';

class PhpCodeSnifferReportTask extends Task
{
	private $format = "details";
	private $styledir = "";
	private $todir = "";

	/** the directory where the results XML can be found */
	private $infile = "phpcs_testresults.xml";

	/**
	 * Set the filename of the XML results file to use.
	 */
	function setInfile($infile)
	{
		$this->infile = $infile;
	}

	/**
	 * Set the format of the generated report. Must be noframes or frames.
	 */
	function setFormat($format)
	{
		$this->format = $format;
	}

	/**
	 * Set the directory where the stylesheets are located.
	 */
	function setStyledir($styledir)
	{
		$this->styledir = $styledir;
	}

	/**
	 * Set the directory where the files resulting from the
	 * transformation should be written to.
	 */
	function setTodir($todir)
	{
		$this->todir = $todir;
	}

	/**
	 * Returns the path to the XSL stylesheet
	 */
	private function getStyleSheet()
	{
		$xslname = "phpcs-" . $this->format . ".xsl";

		if ($this->styledir)
		{
			$file = new PhingFile($this->styledir, $xslname);
		}
		else
		{
			$path = Phing::getResourcePath("phing/etc/$xslname");

			if ($path === NULL)
			{
				$path = Phing::getResourcePath("etc/$xslname");

				if ($path === NULL)
				{
					throw new BuildException("Could not find $xslname in resource path");
				}
			}

			$file = new PhingFile($path);
		}

		if (!$file->exists())
		{
			throw new BuildException("Could not find file " . $file->getPath());
		}

		return $file;
	}

	/**
	 * Transforms the DOM document
	 */
	private function transform(DOMDocument $document)
	{
		$dir = new PhingFile($this->todir);

		if (!$dir->exists())
		{
			throw new BuildException("Directory '" . $this->todir . "' does not exist");
		}

		$xslfile = $this->getStyleSheet();

		$xsl = new DOMDocument();
		$xsl->load($xslfile->getAbsolutePath());

		$proc = new XSLTProcessor();
		$proc->importStyleSheet($xsl);

		$writer = new FileWriter(new PhingFile($this->todir, 'phpcs-' . $this->format . '.html'));
		$writer->write($proc->transformToXML($document));
        $writer->close();
	}

	/**
	 * The main entry point
	 *
	 * @throws BuildException
	 */
	public function main()
	{
		$phpcsTestResults = new DOMDocument();
		$phpcsTestResults->load($this->infile);

		$this->transform($phpcsTestResults);
	}
}
