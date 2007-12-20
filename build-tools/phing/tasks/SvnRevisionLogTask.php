<?php
/**
 * $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Stores the number of the last revision of a workingcopy in a property
 *
 * @author Michiel Rook <michiel.rook@gmail.com>
 * @version $Id$
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.1.0
 */
class SvnRevisionLogTask extends SvnBaseTask
{
	private $propertyName = "svn.revisionlog";

	private $maxLogEntries = 0;

	/**
	 * Sets the name of the property to use
	 */
	function setPropertyName($propertyName)
	{
		$this->propertyName = $propertyName;
	}

	/**
	 * Returns the name of the property to use
	 */
	function getPropertyName()
	{
		return $this->propertyName;
	}

	/**
	 * Sets the number of log entries to get
	 */
	function setMaxlogentries($maxlogentries)
	{
	    $this->maxLogEntries = $maxlogentries;
	}

	/**
	 * Returns the number of log entries to get
	 */
	function getMaxlogentries()
	{
	    return $this->maxlogentries;
	}

	/**
	 * The main entry point
	 *
	 * @throws BuildException
	 */
	function main()
	{
		$this->setup('log');

		$logEntries = $this->run();

		if(is_array($logEntries) && count($logEntries) > 0) {
            $numLogEntries = count($logEntries);
		    $xmlstring = '<svnlog>';
		    for ($i=0; $i<$numLogEntries; $i++) {
		        if (($this->maxLogEntries == 0) || ($i < $this->maxLogEntries)) {
		            $xmlstring.= '<entry>';
		            $xmlstring.= '<revision>' . $logEntries[$i]['REVISION']. '</revision>';
		            $xmlstring.= '<author>' . $logEntries[$i]['AUTHOR'] . '</author>';
		            $xmlstring.= '<date>' . $logEntries[$i]['DATE'] . '</date>';
		            $xmlstring.= '<msg><![CDATA[' . $logEntries[$i]['MSG'] . ']]></msg>';
		            $xmlstring.= '</entry>';
		        } else {
		            break;
		        }
		    }
		    $xmlstring.= '</svnlog>';
		    $this->project->setProperty($this->getPropertyName(), $xmlstring);
		}
		else
		{
			throw new BuildException("Failed to parse the output of 'svn info'.");
		}
	}

}
