<?php

class Robofile extends \Robo\Tasks
{
    public function release()
    {
        $version = file_get_contents('VERSION');
        // ask for changes in this release
        $changelog = $this->taskChangelog()
            ->version($version)
            ->askForChanges();

        // adding changelog and pushing it
        $this->taskExec('git add CHANGELOG.md')->run();
        $this->taskExec('git commit -m "updated changelog"')->run();
        $this->taskExec('git push')->run();

        // create GitHub release
        $this->taskGitHubRelease($version)
            ->uri('Codeception/Specify')
            ->askDescription()
            ->changes($changelog->getChanges())
            ->run();
    }


}