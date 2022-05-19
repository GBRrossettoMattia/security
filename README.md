Fxp Security
============

[![Latest Version](https://img.shields.io/packagist/v/fxp/security.svg)](https://packagist.org/packages/fxp/security)
[![Build Status](https://img.shields.io/travis/fxpio/fxp-security/master.svg)](https://travis-ci.org/fxpio/fxp-security)
[![Coverage Status](https://img.shields.io/coveralls/fxpio/fxp-security/master.svg)](https://coveralls.io/r/fxpio/fxp-security?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/fxpio/fxp-security/master.svg)](https://scrutinizer-ci.com/g/fxpio/fxp-security?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/6951c069-4ec4-4cfa-a3b9-281085553fdb.svg)](https://insight.sensiolabs.com/projects/6951c069-4ec4-4cfa-a3b9-281085553fdb)

The Fxp Security Component is a Extended Role-Based Access Control (E-RBAC) including the management of roles,
role hierarchy, groups, and permissions with a granularity ranging from global permission to permission for
each field of each object. With the sharing rules, it's possible to define users, groups, roles or permissions
for each record of an object. In this way, a user can get more permissions due to the context defined by the
sharing rule.

Features include:

- Compatible with Symfony Security and user manager library (ex. [Friends Of Symfony User Bundle](https://github.com/FriendsOfSymfony/FOSUserBundle))
- Compatible with [Doctrine extensions](https://github.com/Atlantic18/DoctrineExtensions)
- Define the roles with hierarchy in Doctrine
- Define the groups with her roles in Doctrine
- Define the user with her roles and groups in Doctrine
- Define the organization with her roles in Doctrine (optional)
- Define the organization user with her roles and groups in Doctrine (optional)
- Defined the permissions on the roles in Doctrine
- Defined the permissions on the sharing entry in Doctrine
- Defined the permissions in the configuration (with global config permissions in Doctrine)
- Defined the roles on the sharing entry in Doctrine
- Share each records by user, role, groups or organization and defined her permissions and roles
- Merge the permissions of roles children of associated roles with user, role, group, organization, sharing entry, and token
- Security Identity Manager to retrieving security identities from tokens (current user,
  all roles, all groups and organization)
- AuthorizationChecker to check the permissions for objects
- Permission Manager to retrieve the permissions with her operations
- Sharing Manager to retrieve the sharing entry with her permissions and roles
- Symfony validators of permission and sharing model
- Permission Voter to use the Symfony Authorization Checker
- Define a role for various host with direct injection in token (regex compatible)
- Execution cache system and PSR-6 Caching Implementation for the permissions getter
- Execution cache and PSR-6 Caching Implementation for the determination of all roles in
  hierarchy (with user, group, role, organization, organization user, token)
- Doctrine ORM Filter to filtering the records in query defined by the sharing rules (compatible with doctrine caches)
- Doctrine Listener to empty the record field value for all query type
- Doctrine Listener to keep the old value in the record field value if the user has not the permission of action
- Organization with users and roles
- Authorization expression voter with injectable custom variables (to build custom expression functions with dependencies)
- `is_basic_auth` expression language function
- `is_granted` expression language function

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this library:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This library is under the MIT license. See the complete license:

[LICENSE](LICENSE)

About
-----

Fxp Security is a [François Pluchino](https://github.com/francoispluchino) initiative.
See also the list of [contributors](https://github.com/fxpio/fxp-security/graphs/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/fxpio/fxp-security/issues).
