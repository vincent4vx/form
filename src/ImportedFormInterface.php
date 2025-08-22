<?php

namespace Quatrevieux\Form;

/**
 * Type for form with imported value
 *
 * @template T as object
 * @extends FilledFormInterface<T>
 *
 * @see FormInterface::import() For create an imported form
 */
interface ImportedFormInterface extends FilledFormInterface {}
