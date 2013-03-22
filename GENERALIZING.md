# A theory about generalizing this code

This code was written at Etsy to meet our specific needs and with a
strong goal of making the code as simple as possible to understand.
Which means that there are places where stuff is hardwired because we
didn’t need the flexibility to do things another way.

Obviously, some of the concepts embedded in this code are not going to
be applicable outside the Etsy context. Thus if you want to use this
code in a different context you have two choices: fork and hack or
generalize. I’d actually suggest you start with the former. Hopefully
things are structured well enough that if you rip out the
Etsy-specific code you’ll be left with a few obvious holes to fill in
with your own stuff. I’ve started things down that path for you by
turning several methods into no-ops and marking with the comment
“IMPLEMENT FOR YOUR CONTEXT”.

That said, if anyone is intersted in generalizing this code, I've also
made a quick start at that in the `generalized` branch. Note that the
code in this branch is completely untested and may still be
wrongheaded in many ways.

The basic approach I took in that branch was to introduce a new
abstraction, the "experimental unit". Every feature is tested relative
to some kind of experimental unit which is named in the feature's
configuration (under the `unit` key) though the Feature_World
implementation can provide a default. Each kind of experimental unit
can support:

- explicit configuration of variants based on some characteristic of
  the unit.

- different bucketing schemes.

As an example, the `Feature_EtsyRequestUnit` class, implements an
experimental unit that maps to a web request. Each web request (in the
Etsy context) has some information about the user who made the request
(at least a cookie called the UAID and possibly an Etsy user ID if
they are signed in). Additionally the request itself may have included
a `features` query param that specifies specific variants for specific
features and may also be an "internal" request, coming from someone
within Etsy.

The configuration syntax for a feature configured with this
experimental unit (which is the default in the current implementation
of `Feature_World`) can be configured with `users`, `groups`, `admin`,
and `internal` keys, that specify variants to be assigned to specific
users, users in specific groups, all Etsy employees (called "admin"),
or for internal requests.

This experimental unit also supports three bucketing styles: 'uaid',
'user', and 'random'. The 'uaid' style uses the cookie that is set on
every request as the bucketing ID while the 'user' style uses the user
ID of signed in users. Random bucketing, which assigns a variant
randomly on each request, is only used for operational ramupus without
user-visible effects such as switching from one backend database to
another.

When a call is made to `Feature::isEnabled` or `Feature::variant`, the
experimental unit is responsible for saying whether a specific variant
should be used (via the `assignedVariant` method) and, if not, what id
should be used for bucketing the experimental unit into a variant (via
`bucketingID`).

In the generalized branch, both those methods take a second argument,
`$data`, which is passed along to the `assignedVariant` and
`bucketingID` methods. In general, implementations of these methods
should ensure that any data they are passed is of the appropriate
type: there is an obligation on callers of the Feature API methods to
pass the appropriate kind of date for the kind of experimental unit
the feature has been configured with.

One thing this generalization does is get rid of the need for the
`isEnabledFor`/`variantFor` and
`isEnabledBucketingBy`/`variantBucketingBy` methods. The main use
case, at Etsy, for the former pair is if we wanted to run an
experiment where insted of bucketing by the user making a request, we
want to bucket by the user who owns the shop the user is looking at.
In the current API, that is achieved by passing the user object
representing the shop owner to `isEnabledFor` and `variantFor`. And
the use case for the `bucketingBy` methods is when we want to bucket
on something that doesn't necessarily have an associated user. For
instance if we wanted to run an experiment on a random selection of
searches, we might use the search terms as the second argument to the
`bucketingBy` methods.

In the generalized API, in the first case we would instead configure a
feature with a `unit` of, say, 'seller' that would map to a class that
either expects some object reperesenting the seller to be passed to
the Feature API calls or which knows how to figure out the seller from
the context of the request. And in the second case we would configure
a query with a `unit` of 'query' that maps to a class that expects a
query string passed as the `$data` argument to the Feature methods.
With such a class, we could then allow the feature to be configured to
return a specific variant for specific queries, e.g. if we want to
ensure that certain very popular queries are kept out of the treatment
group for whatever reason.