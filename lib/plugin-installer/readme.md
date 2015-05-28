## About me

This is rtMedia lib to be used in rtMedia add-ons only. It will show admin notice if rtMedia is not installed or activated
and will install/activate rtMedia on single click via ajax.

Use this lib as a subtree in your rtMedia add-on.

To use this lib, first add this repo as a remote.

	git remote add rtm-pi-subtree git@git.rtcamp.com:rtmedia/rtm-plugin-installer.git

Run following command to add remote repo as subtree

	git subtree add --prefix=lib/plugin-installer rtm-pi-subtree master

To pull changes subtree, run following command

	git subtree pull --prefix=lib/plugin-installer rtm-pi-subtree master

## Resources for subtree:

[subtree explained on wpveda](http://wpveda.com/git/subtree.html)

[tutorial on medium](https://medium.com/@v/git-subtrees-a-tutorial-6ff568381844)
