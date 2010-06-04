<?php

// Like file_get_contents(), but for a process.
function proc($process, $asArray = true)
{
    $result = $asArray ? array() : '';
    if (($pp = popen($process, 'r')) === false) {
        return $result;
    }
    while (($line = fgets($pp)) !== false) {
        if ($asArray) {
            array_push($result, rtrim($line));
        } else {
            $result .= $line;
        }
    }
    pclose($pp);
    return $result;
}

// Get the local configuration.
$cfg = new stdClass;
require_once 'config.php';

// Abort if we're not yet configured.
if (empty($cfg->repositories)) {
    echo 'You must edit config.php first.';
    exit;
}

// Verify we have a valid repo.
$repoId = trim($_GET['r']);
if (!array_key_exists($repoId, $cfg->repositories)) {
    echo 'no such repository "' . $repoId . '"';
    header('HTTP/1.1 404 Page Not Found');
    exit;
}
$repoPath = $cfg->repositories[$repoId]['path'];
$repoName = $cfg->repositories[$repoId]['name'];

// Get the description for this repo.
$descFile = $repoPath . '/description';
if (!is_readable($descFile)) {
    $repoDesc = file_get_contents($descFile);
} else {
    $repoDesc = $repoName . ' repository';
}

// Get the branches for this repo.
$branches = array();
foreach (proc("$cfg->git --git-dir=$repoPath branch") as $branch) {
    array_push($branches, trim(substr($branch, 2)));
}

// Verify we have a valid branch.
$branch = trim($_GET['b']);
if (!in_array($branch, $branches)) {
    echo 'no such branch "' . $branch . '"';
    header('HTTP/1.1 404 Page Not Found');
    exit;
}

// Create the feed object.
set_include_path($cfg->zfPath);
require_once 'Zend/Feed/Writer/Feed.php';
$feed = new Zend_Feed_Writer_Feed();
$feed->setTitle($repoName . ' commit log - ' . $branch);
$feed->setDescription($repoDesc);
$feed->setLink($cfg->viewGitUri);
$feed->setFeedLink(
    ($_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], $cfg->type
);

$feed->addAuthor(array(
    'name' => $cfg->authorName,
    'email' => $cfg->authorEmail
));

// We'll set the feed's modification date to the date of the most recent commit.
$timeModified = 0;

// Loop over the last N commits.
foreach (proc("$cfg->git --git-dir=$repoPath log $branch -$cfg->numEntries --format=format:'%H|%ct|%an|%ae|%s'") as $log) {

    list($commitId, $commitTime, $authorName, $authorEmail, $comment) = explode('|', $log, 5);

    // If this is the most recent commit, we'll use it for the feed's modification date.
    if ($commitTime > $timeModified) {
        $timeModified = $commitTime;
    }

    // We'll use the commit diff as the content.
    $diff = '<pre>' . htmlentities(proc("$cfg->git --git-dir=$repoPath show --format=$cfg->format $commitId", false)) . '</pre>';

    // Build the entry for this commit.
    $entry = $feed->createEntry();
    $entry->setTitle($authorName . ' : ' . $comment);
    $entry->setDescription($comment);
    $entry->setLink($cfg->viewGitUri . '?a=commitdiff&p=' . urlencode($repoName) . '&h=' . $commitId);
    $entry->addAuthor(array(
        'name' => $authorName,
        'email' => $authorEmail
    ));
    $entry->setContent($diff);
    $entry->setDateModified($commitTime);
    $feed->addEntry($entry);

}

$feed->setDateModified($timeModified);
echo $feed->export($cfg->type);
