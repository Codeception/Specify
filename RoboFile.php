<?php

class Robofile extends \Robo\Tasks
{
    public function release()
    {
        $version = file_get_contents('VERSION');
        // ask for changes in this release
        $changelog = $this->taskChangelog()
            ->version($version)
            ->askForChanges()
            ->run();

        // adding changelog and pushing it
        $this->taskGit()
            ->add('CHANGELOG.md')
            ->commit('updated changelog')
            ->push()
            ->run();

        // create GitHub release
        $this->taskGitHubRelease($version)
            ->uri('Codeception/Specify')
            ->askDescription()
            ->run();
    }


}