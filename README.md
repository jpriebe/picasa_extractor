# picasa_extractor
PHP script and classfiles for recursively finding picasa.ini files and parsing them.
The intent is to gather information about a large picasa library to facilitate
migrating the library to another platform (in my case, I'm interested in migrating
to Amazon Prime Photos).

The code extracts vital meta data:

- album membership
- faces
- keywords (tags)
- stars

For a platform like Amazon Prime Photos, which doesn't support tags or stars, the
code will help build designated albums to group tagged images and separate the starred
images from the rest.

