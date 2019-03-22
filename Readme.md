# meta-tag [![Build Status](https://travis-ci.org/cumuru/meta-tag.svg?branch=master)](https://travis-ci.org/cumuru/meta-tag)

TYPO3 extension allowing to write your meta-tags in
Fluid. Syntax is a close as possible to the HTML
`<meta>` tag. Ships with one level of override.

## Installation

Via composer

    composer require undkonsorten/meta-tags
    
or from [Github](https://github.com/cumuru/meta-tag)

## Configuration

No configuration is needed, there‘s no TypoScript to include.

## Usage

To use the view helper in your Fluid templates you have to
import the namespace, e.g.:

    <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
            xmlns:m="http://typo3.org/ns/Undkonsorten/MetaTag/ViewHelpers"
            data-namespace-typo3-fluid="true">
            
or 

    {namespace m=Undkonsorten\MetaTag\ViewHelpers}
    
Then you can use the meta view helper in any of the following forms:

    <!--Tag style-->
    <m:meta http-equiv="content-type" content="{settings.contentType}" />
    <!--Inline style-->
    {m:meta(property:'og:description',content:description)}
    <!--You can omit content attribute and use tag content instead-->
    <m:meta property="og:image"><f:uri.image image="{image}" width="800" height="600" absolute="1" /></m:meta>
    <!--The same works for inline syntax-->
    {author.fullName -> m:meta(name:'author')}
    
If the content is empty (i.e. only white-space characters) no
meta tag is added. There‘s no need to wrap each of your meta tags
in an if construct just to get rid of the empty ones.

### Overriding

If for a requested meta tag there‘s a collision with an existing meta tag 
(same `property` / `name` / `http-equiv` value) then the existing one will
be kept and the new one will be discarded. To change this behavior you can
set `override="1"`. Then any existing meta tag of the same
type will be overridden.

That means you have a two-level hierarchy that works regardless
of processing order at run time. Just define your "global" meta tag
with values e.g. from `pages` record without override but set that flag 
for your more specific ones, e.g. coming from extension show actions.  