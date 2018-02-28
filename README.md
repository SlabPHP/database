# SlabPHP Database Library

Every framework needs some way of wrapping/organizing database stuff right? The SlabPHP database library adds two concepts to the overall framework. The first is an abstraction layer between database providers and to provide variable replacements in query messages. The second is a lightweight data object model for building solid object databaes return objects.

This library was originally built to provide interface abstraction and wrap various mysql functions. They were eventually deprecated and the rest of the framework relied on this abstraction layer so a new provider was created that wrapped a mysqli class.

//@todo incomplete code check-in, needs tests, mysql join helper, automapping stuff may be fubar