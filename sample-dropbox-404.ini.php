; <?php exit; __halt_compiler(); Prevent direct access
;
; Do not edit this file! Rename if to dropbox-404.ini.php
; and place it at wp-content/dropbox-404.ini.php

[aliases]
	; Reserved keywords redirect to URL.
	bc          =	"http://brainstormmedia.basecamphq.com"
	basecamp 	=	"http://brainstormmedia.basecamphq.com"
	git         =	"http://git.brainstormmedia.com"
	mail        =	"http://mail.google.com/a/brainstormmedia.com"
	docs        =	"http://docs.google.com/a/brainstormmedia.com"
	calendar    =	"http://calendar.google.com/a/brainstormmedia.com"

	; Reserved keywords run method
	files       =	"search_dropbox"
	file        =	"search_dropbox"
	f           =	"search_dropbox"

	; Directories on this web server to search for file matches
	local_directories[] = ""

[dropbox]
	; Paul
	username         = "" ; E-mail address
	password         = ""
	consumer_key     = "" ; Developer key. See https://www.dropbox.com/developers/quickstart
	consumer_secret  = "" ; Developer key. See https://www.dropbox.com/developers/quickstart
	token            = "" ; Returned by code. Store this instead of username/pass
	token_secret     = "" ; Returned by code. Store this instead of username/pass
	directories[]    = "/Public"
	;directories[]    = "/some/private/directory"