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

    <dt id="term-embed">Include</dt>
    <dd>Data usually has relationships to other data. Users have posts, posts have 
    comments, comments belong to posts, etc. When represented in RESTful APIs this data is usually 
    data is "included" (a.k.a embedded or nested) into the resource.
    A transformer will contain <code>includePosts()</code> methods, which will expect a resource to be 
    returned, so it can be placed inside the parent resource.</dd>

    <dt id="term-manager">Manager</dt>
    <dd>Fractal has a class named <em>Manager</em>, which is responsible for maintaining 
    a record of what embedded data has been requested, and converting the nested data into
    arrays, JSON, YAML, etc. recursively.</dd>

    <dt id="term-pagination">Pagination</dt>
    <dd><em>Pagination</em> is the process of dividing content into pages, which in 
    relation to Fractal is done in two alternative ways: <em>Cursors</em> 
    and <em>Paginators</em>.</dd>

    <dt id="term-paginator">Paginator</dt>
    <dd>A <em>paginator</em> is an intelligent form of <en>Pagination</en>, which will require 
    a total count of how much data is in the database. This adds a <code>"paginator"</code> item to 
    the response meta data, which will contain next/previous links when applicable.</dd>

    <dt id="term-resource">Resource</dt>
    <dd>A <em>resource</em> is an object which acts as a wrapper for generic data. A <em>resource</em> will
    have a <em>transformer</em> attached, for when it is eventually transformed ready to be serialized and output.</dd>

    <dt id="term-scope">Serializer</dt>
    <dd>A <em>Serializer</em> structures your <em>Transformed</em> data in certain ways. REST has many output structures, two popular ones being HAL and JSON-API. Twitter and Facebook output data differently to each other, and Google does it differently too. <em>Serializers</em> let you switch between various output formats 
    with minimal effect on your <em>Transformers</em>.</dd>

    <dt id="term-transformer">Transformer</dt>
    <dd><em>Transformers</em> are classes, or anonymous functions, which are responsible for taking 
    one instance of the resource data and converting it to a basic array. This process is done to obfuscate
    your data store, avoiding [Object-relational impedance mismatch] and allowing you to even glue various 
    elements together from different data stores if you wish. The data is taken from these complex data store(s) and made into a format that is more manageable, and ready to be <em>Serialized</em>.</dd>

</dl>

[Object-relational impedance mismatch]: https://en.wikipedia.org/wiki/Object-relational_impedance_mismatch