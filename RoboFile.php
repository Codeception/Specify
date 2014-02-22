<?php

class Robofile extends \Robo\Tasks
{
    public function changelog()
    {
        // ask for changes in this release
        $changelog = $this->taskChangelog()
            ->version(\Robo\Runner::VERSION)
            ->askForChanges();

        // adding changelog and pushing it
        $this->taskExec('git add CHANGELOG.md')->run();
        $this->taskExec('git commit -m "updated changelog"')->run();
        $this->taskExec('git push')->run();

        // create GitHub release
        $this->taskGitHubRelease(\Robo\Runner::VERSION)
            ->uri('Codegyre/Robo')
            ->askDescription()
            ->changes($changelog->getChanges())
            ->run();
    }


}