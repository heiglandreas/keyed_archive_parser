# NSKeyedArchive-Parser

Parse Apples NSKeyedArchiver-files

NSKeyedArchive-Files are a kind of plist-file that can be parsed using a plist-file parser.

But the results will not be what you'd expect. This parser can handle such files and will return a plist-file that you can then handle.

It would not have been possible without the awesome work of [Sarah Edwards](https://twitter.com/iamevltwin) on her [blog](https://www.mac4n6.com/blog/2016/1/1/manual-analysis-of-nskeyedarchiver-formatted-plist-files-a-review-of-the-new-os-x-1011-recent-items)! 

## Installation

This is best installed using `composer`

```bash
composer require org_heigl/nskeyedarchiverparser
```


## Usage

```php
use CFPropertyList\CFPropertyList;
use use Org_Heigl\NSKeyedArchiver\Parser;

$archive = new CFPropertyList('path/to/file.plist');
$parser  = new Parser($archive);

$readableArchive = $parser->parse();

// $readableArchive is an instance of 
// CFPropertyList
```
