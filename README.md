# feature

Etsy's Feature flagging API used for operational rampups and A/B
testing. This API provides a facility for PHP code to check whether a
particular feature is enabled and to control what features are enabled
from a config file, with options to enable a feature for a certain
percentage of users (useful for both operational rampups and A/B
testing) as well as to enable certain features for specific users or
classes of users. It supports both simple features that are either on
or off or multi-variant features with named variants.
