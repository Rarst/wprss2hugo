# wprss2hugo — WordPress to Hugo importer
_Go static. Hugo static._

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Rarst/wprss2hugo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Rarst/wprss2hugo/?branch=master)
![Packagist Version](https://img.shields.io/packagist/v/Rarst/wprss2hugo)
![PHP from Packagist](https://img.shields.io/packagist/php-v/Rarst/wprss2hugo)
[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg)](https://github.com/php-pds/skeleton)

wprss2hugo is an importer from the [WordPress](https://wordpress.org/) eXtended RSS export file format to the [Hugo](https://gohugo.io/) static site generator.

It aims to be comprehensive and reasonably flexible, but mostly to lower my hosting bill.

## Install

wprss2hugo is a command line PHP 7.3+ project and installs with [Composer](https://getcomposer.org/):

```bash
composer create-project rarst/wprss2hugo
```

## Use

```bash
cd wprss2hugo
php bin/wprss2hugo.php example.WordPress.YYYY-MM-DD.xml
```

Results are generated in the `output` folder.

_Note:_ WordPress might not store and export valid HTML paragraphs markup. You might want to add something like `add_filter( 'the_content_export', 'wpautop' );` to the WP installation before export.

### Command line arguments

```bash
php bin/wprss2hugo.php --help

Arguments:
  file                      Path to a WordPress export XML file.
Options:
      --content-type=       html|md [default: "html"]
      --front-matter-type=  yaml|toml|json [default: "yaml"]
      --data-type=          yaml|toml|json [default: "yaml"]
```

_Note:_ conversion to Markdown for the post content is best effort and might be suboptimal on complex markup.

_Note:_ TOML format is not meant for data, data files in TOML will have the data assigned to a dummy `data` root key.

## Data map

Source | Destination
-|-
site title, URL, description | `config.[data type]`
posts, pages, attachments, custom post types | `content/[post type]/[slug].[content type]` 
tags, categories, formats, terms | `content/[taxonomy]/[term]/_index.[content type]`
authors | `content/authors/[login]/_index.[content type]` (taxonomy)
comments | `data/comments/[post ID].[data type]`

## Data retrieval

### Attachments

Attachments are stored as `attachment` page type and can be retrieved by a parent post ID:

```go
{{ $attachments := where (where .Site.Pages "Type" "attachment") "Params.parentid" .Params.id }}

{{ with $attachments }}
    <h2>Attachments</h2>
    {{ range . }}
        <img src="{{ .Params.attachmenturl }}"
            {{ with .Params.meta._wp_attachment_image_alt }}alt="{{ . }}"{{ end }} />
    {{ end }}
{{ end }}
```

### Comments

Comments are stored as data files and can be retrieved by a parent post ID:

```go
{{ with .Site.Data.comments }}
    {{ with index . (string $.Page.Params.id) }}
        <h2>Comments</h2>
        <ul>
            {{ range . }}
                <li>{{ .author }} says: {{ .content | safeHTML }}</li>
            {{ end }}
        </ul>
    {{ end }}
{{ end }}
```