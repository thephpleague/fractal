---
layout: layout
title: Glossary
---

Glossary
========

Learn more about the general concepts of Fractal.

<dl class="glossary">

    <dt id="term-cursor">Cursor</dt>
    <dd>A <em>cursor</em> is an unintelligent form of <en>Pagination</en>, which does 
    not require a total count of how much data is in the database. This makes 
    it impossible to know if the "next" page exists, meaning an API client would 
    need to keep making HTTP Requests until no data could be found (404).</dd>

    <dt id="term-embed">Embed</dt>
    <dd></dd>

    <dt id="term-manager">Manager</dt>
    <dd>Fractal has a class named Manager, which is responsbile for maintaining 
    a record of what embedded data has been requested, and converting the nested 
    data into JSON recursively.</dd>

    <dt id="term-pagination">Pagination</dt>
    <dd><em>Pagination</em> is the process of dividing content into pages, which in 
    relation to Fractal is done in two alternatve ways: <em>Cursors</em> 
    and <em>Paginators</em>.</dd>

    <dt id="term-paginator">Paginator</dt>
    <dd></dd>

    <dt id="term-resource">Resource</dt>
    <dd></dd>

    <dt id="term-scope">Scope</dt>
    <dd></dd>

    <dt id="term-transformer">Transformer</dt>
    <dd></dd>

</dl>