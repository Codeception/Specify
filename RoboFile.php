<?php

class Robofile extends \Robo\Tasks
{
    public function release()
    {
        $this->test();

        $version = file_get_contents('VERSION');

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

    public function changed($description)
    {
        $this->taskChangelog()
            ->version(file_get_contents('VERSION'))
            ->change($description)
            ->run();
    }


    public function test()
    {
        $res = $this->taskPHPUnit()->run();
        if (!$res) exit;
    }
}