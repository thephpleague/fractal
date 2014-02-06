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
    <dd>Data - regardless of the source - usually has relationships. Users have posts, posts have 
    comments, comments belong to posts, etc. When represented in RESTful APIs this data is usually 
    embedded (or nested) into the main response. Fractal calls these embedded resources: <em>embeds</em>.
    A transformer will contain <code>embedFoo()</code> methods, which allow for resource embedding to occur.</dd>

    <dt id="term-manager">Manager</dt>
    <dd>Fractal has a class named <em>Manager</em>, which is responsible for maintaining 
    a record of what embedded data has been requested, and converting the nested 
    data into JSON recursively.</dd>

    <dt id="term-pagination">Pagination</dt>
    <dd><em>Pagination</em> is the process of dividing content into pages, which in 
    relation to Fractal is done in two alternative ways: <em>Cursors</em> 
    and <em>Paginators</em>.</dd>

    <dt id="term-paginator">Paginator</dt>
    <dd>A <em>paginator</em> is an intelligent form of <en>Pagination</en>, which will require 
    a total count of how much data is in the database. This adds a <code>"paginator"</code> namespace to 
    the data response, which will contain meta data and next/previous links when applicable.</dd>

    <dt id="term-resource">Resource</dt>
    <dd>A <em>resource</em> is an object which acts as a wrapper for data. A <em>resource</em> will
    have a <em>transformer</em> attached, for when it is eventually transformed/output.</dd>

    <dt id="term-scope">Scope</dt>
    <dd><em>Resources</em> can be <em>embedded</em> inside other <em>resources</em>, but only if it 
    is within the correct scope. For example, if <code>books.comments</code> are requested, only comments directly 
    related to the book will be displayed, not comments on the authors profile. <em>Scopes</em> are 
    objects which track their location in the hierarchy, and control input or output.</dd>

    <dt id="term-transformer">Transformer</dt>
    <dd><em>Transformers</em> are classes, or anonymous functions, which are responsible for taking 
    one instance of the resource data and converting it to a basic array. This array can then be turned 
    into JSON, YML, or any other data structure.</dd>

</dl>