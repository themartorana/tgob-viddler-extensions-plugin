TGOB Viddler Extension plugin for Wordpress
============================================

Viddler extensions to PHPViddler used on http://twoguysonbeer.com - All of these functions are meant to be used in 
conjunction with the Viddler player API, and are used to take posts with a `[viddler ...]` shortcut code in them
and lay them out as desired.

In `tgob-viddler.php` edit the `$VIDDLER_API_KEY` variable to be your API key.

This relies on the `[viddler... ]` shortcut tag to function. Ex: 

    [viddler id-5409fbe6 h-535 w-343]

The functions
-------------

`tgob_excerpt()` will get an excerpt of the wordpress post without the TGOB player.

`tgob_viddler_thumbnail(...)` will return an appripriate thumbnail of your video from Viddler.

`tgob_viddler_player_embed(...)` will embed the player where you wish it to be embedded.

`tgob_post_without_player()` will get the entire post sans-player.

How they're used on the Two Guys on Beer site
-------------------------------------------

All of this is noticed on the homepage. The main feature uses `tgob_viddler_player` to get the main player, while posting the `tgob_excerpt()` on the right hand side.
All of the videos underneath use `tgob_viddler_thumbnail(...)` along with `tgob_exceprt()`.

Single post pages use `tgob_viddler_player_embed(...)` on top of `tgob_post_without_player()` 

Use the functions inclued in the plugin in your templates.
