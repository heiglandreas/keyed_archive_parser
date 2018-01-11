<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @since     27.12.2017
 * @link      http://github.com/heiglandreas/org.heigl.KeyedArchiverParser
 */

namespace Org_Heigl\NSKeyedArchiver;

use CFPropertyList\CFArray;
use CFPropertyList\CFDictionary;
use CFPropertyList\CFPropertyList;
use CFPropertyList\CFString;
use CFPropertyList\CFType;
use Org_Heigl\NSKeyedArchiver\Exception\InvalidPlistFormat;
use Org_Heigl\NSKeyedArchiver\Exception\NoPlistRootFound;

class Parser
{
    /**
     * This is the offset from the UNIX-epoch (1.1.1970 00:00:00 UTC) to the
     * Apple-Epoch (1.1.2001 00:00:00 UTC)
     */
    const EPOCH_OFFSET = 978307200;

    private $objects;

    private $plist;

    private $topLevel;

    private $root;

    public function __construct(CFPropertyList $plist)
    {
        $this->plist    = $plist;
        $this->topLevel = $this->plist->get(0);

        if (! $this->topLevel instanceof CFDictionary) {
            throw new InvalidPlistFormat('Not a valid format');
        }


        $this->root    = $this->topLevel->get('$top')->get('root')->getValue();
        $this->objects = $this->topLevel->get('$objects');

    }

    public function parse()
    {

        $newPlist = new CFPropertyList();

        $baseDict = $this->objects->get($this->root);

        $newPlist->add($this->parseDictionary($baseDict));

        return $newPlist;
    }

    private function parseDictionary(CFDictionary $dictionary) : CFDictionary
    {
        $newDictionary = new CFDictionary();

        foreach ($dictionary->get('NS.keys') as $key => $item) {

            $name  = $this->objects->get($item->getValue())->getValue();
            $value = $this->objects->get($dictionary->get('NS.objects')->get($key)->getValue());
            $value = $this->handleObject($value);

            $newDictionary->add($name, $value);
        }

        return $newDictionary;
    }

    private function parseArray(CFDictionary $dictionary) : CFArray
    {
        $array = new CFArray();
        foreach ($dictionary->get('NS.objects') as $item) {

            $array->add($this->handleObject($this->objects->get($item->getValue())));
        }

        return $array;
    }

    private function handleObject(CFType $value) : CFType
    {
        if ($value instanceof CFDictionary && $value->get('NS.keys')) {
            $value = $this->parseDictionary($value);
        }

        if ($value instanceof CFDictionary && $value->get('NS.objects')) {
            $value = $this->parseArray($value);
        }

        if ($value instanceof CFDictionary && $value->get('NS.string')) {
            $value = $value->get('NS.string');
        }

        if ($value instanceof CFDictionary && $value->get('NS.time')) {
            $value = $value->get('NS.time');
        }

        return $value;
    }
}
