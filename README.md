# Infrangible Catalog Attribute Landing Page

Landing pages for product attributes: a page under its own URL that lists the products with up to five attribute
values (for example all red products, or red tops in size M), with the layered navigation, sorting, texts, images and
CMS blocks of a category page. Landing groups list the landing pages with the same attributes, grouped by their values.

## Admin

Infrangible > Attribute Landing Pages:

- Pages: URL key, headline, description, CMS block, additional CMS block, SEO CMS block, image, logo, thumbnail, up to
  five filterable attributes with their values (the first is required and can have several values, which are combined
  with "or"), attribute set, the attributes the products can be sorted by with the default sort attribute and
  direction, store views, active, page title, meta description and meta keywords
- Groups: URL key, headline, description, CMS block, up to five filterable attributes, store views, active, page title,
  meta description and meta keywords

The URL key of a page or group must not be used by another page or group (also an inactive one), a CMS page or a URL
rewrite of the store views. The full page cache is cleaned when a page or group is saved.

## Landing pages

Every active page has its URL key with the URL suffix of categories, for example `red.html`. It shows the products
with its attribute values in the layered navigation: the attributes of the page are applied, but neither offered nor
shown as active filters; all other filterable attributes can be filtered further. The page has the layout handles

- `infrangible_catalogattributelandingpage_page_view`
- `infrangible_catalogattributelandingpage_page_view_<attribute codes>`, for example `…_page_view_color_size`
- `infrangible_catalogattributelandingpage_page_view_<attribute codes>_<values>`, for example `…_page_view_color_58`
- `infrangible_catalogattributelandingpage_page_view_<page ID>`

so that layout files can change all pages of an attribute or a single page.

## Landing groups

Every active group has its URL key as well. It lists the active pages of the store view that use exactly the
attributes of the group, with a headline per value of the first attributes and the pages, each with its first five
products, below the values of the last attribute.

## Configuration

Stores > Configuration > Infrangible Solutions > Attribute Landing Page:

- Image Position: where the image of a page is shown (before the headline, the description, the CMS block or the
  product list)

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer, with OpenSearch or Elasticsearch
- PHP 8.1 or newer

The pages and groups in the XML sitemap: [Infrangible Catalog Attribute Landing Page
Sitemap](https://www.infrangible.dev/modules/catalog-attribute-landing-page-sitemap).

## License

Infrangible Catalog Attribute Landing Page is licensed under the MIT License - see the LICENSE file for details.
