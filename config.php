<?php

/**
 * The list of repositories to be served, in the following format:
 *
 * $cfg->repositories = array(
 *     'myproj' => array(
 *         'name' => 'My Super Project',
 *         'path' => '/path/to/repository/myproject.git'
 *     ),
 *     'other' => array(
 *         'name' => 'Something Else',
 *         'path' => '/path/to/repository/whatever.git'
 *     )
 * );
 *
 * The primary array key is used as a short identifier for building the feed's
 * URI. The name key is a more descriptive name for the repository, The path
 * value is the full path to the git repository. It should point to the
 * directory with the "config" and "description" files in it. In this example,
 * the feed for the master branch of My Super Project would be at /myproj/master.
 */
$cfg->repositories = array(
);

/**
 * The author name to use for the feed.
 */
$cfg->authorName = 'Your Name';

/**
 * The author email to use for the feed.
 */
$cfg->authorEmail = 'you@yourcompany.com';

/**
 * The number of commits to publish in the feed.
 */
$cfg->numEntries = 10;

/**
 * The path to your git executable. Can be just git if it's already in your path.
 */
$cfg->git = 'git';

/**
 * The git log format to use for the content of the feed entries. Possible values
 * are as described in the git docs:  oneline, short, medium, full, fuller, etc.
 */
$cfg->format = 'fuller';

/**
 * The type of feed to generate. Possible values are rss and atom.
 */
$cfg->type = 'atom';

/**
 * The base URI of your ViewGit installation.
 */
$cfg->viewGitUri = 'http://git.mydomain.com/';

/**
 * The path to Zend Framework. This should point to the directory with the Zend
 * subdirectory in it.
 */
$cfg->zfPath = '/path/to/zf/library';
