# Contributing to joomlagerman/joomla
(german language files for Joomla! 3.9 and up)

:+1::tada: First off, thanks for taking the time to contribute! :tada::+1:

Our [Code of conduct](../CODE_OF_CONDUCT.md). Please read carefully.

Getting started with [git and github](https://guides.github.com/activities/hello-world/). If you don't have git on your machine, [install it]( https://help.github.com/articles/set-up-git/).
#### *If you're not comfortable with command line, [here are tutorials using GUI tools.]( #tutorials-using-other-tools )*

## Working with this repository

### Fork this repository

Fork this repository by clicking on the fork button on the top of this page.
This will create a copy of this repository in your account.

### Clone the repository

Now clone the forked repository to your machine. Go to your GitHub account, open the forked repository, click on the clone button and then click the *copy to clipboard* icon.

Open a terminal and run the following git command:

```
git clone "url you just copied"
```
where "url you just copied" (without the quote marks) is the url to this repository (your fork of this project). See the previous steps to obtain the url.

For example:
```
git clone https://github.com:this-is-you/joomla.git
```
where `this-is-you` is your GitHub username. Here you're copying the contents of the joomla repository on GitHub to your computer.

### Create or take an issue

Go to [Issues](https://github.com/joomlagerman/joomla/issues) and create a new issue or take one open issue to write an PR.  
Normally no PR without a previous issue. We have the *issue-first principle*.

### Create a branch

Change to the repository directory on your computer (if you are not already there):

```
cd joomla
```
Now create a branch using the `git checkout` command:
```
git checkout -b <add-your-new-branch-name>
```

For example:
```
git checkout -b <issue-number>
```
(The name of the branch does not need to have a issue-number as branch name, but it's a easy thing to reference th issue here.)

### Make necessary changes and commit those changes

Now open all files in a text editor or in a IDE e.g. PhpStorm and change it. Now, save the file.

If you go to the project directory and execute the command `git status`, you'll see there are changes.


Add those changes to the branch you just created using the `git add` command:

```
git add .
```

Now commit those changes using the `git commit` command:
```
git commit -m "e.g. fix #<issue-number>"
```
replacing `<issue-number>` with the issue-number.

### Push changes to GitHub

Push your changes using the command `git push`:
```
git push origin <add-your-branch-name>
```
replacing `<add-your-branch-name>` with the name of the branch you created earlier.

### Submit your changes for review

If you go to your repository on GitHub, you'll see a  `Compare & pull request` button. Click on that button.

Now submit the pull request.

We will always try to edit or merge issues and PRs as soon as possible. You will get a notification email once the changes have been merged.

### Where to go from here?

Congrats!  You just completed the standard _fork -> clone -> edit -> PR_ workflow that you'll encounter often as a contributor!
