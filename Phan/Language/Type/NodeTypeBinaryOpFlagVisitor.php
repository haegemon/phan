<?php declare(strict_types=1);
namespace Phan\Language\Type;

use \Phan\Language\AST\FlagVisitorImplementation;
use \Phan\Language\Context;
use \Phan\Language\UnionType;
use \ast\Node;

class NodeTypeBinaryOpFlagVisitor extends FlagVisitorImplementation {

    /**
     * @var Context
     */
    private $context;

    /**
     *
     */
    public function __construct(Context $context) {
        $this->context = $context;
    }

    public function visitBinaryConcat(Node $node) {
        $temp_taint = false;

        UnionType::fromNode(
            $this->context,
            $node->children[0],
            $temp_taint
        );

        if($temp_taint) {
            $taint = true;
            return new UnionType(['string']);
        }

        UnionType::fromNode(
            $this->context,
            $node->children[1],
            $temp_taint
        );

        if($temp_taint) {
            $taint = true;
        }

        return new UnionType(['string']);
    }

    private function visitBinaryOpCommon(Node $node) {
        $left =
            UnionType::fromNode($this->context, $node->chilren[0]);

        $right =
            UnionType::fromNode($this->context, $node->chilren[1]);

        $taint = false;
        // If we have generics and no non-generics on the left and the right is not array-like ...

        if(!empty(generics($left))
            && empty(nongenerics($left))
            && !type_check($right, 'array')
        ) {
            Log::err(
                Log::ETYPE,
                "array to $right comparison",
                $context->getFile(),
                $node->lineno
            );
        } else if(!empty(generics($right))
            && empty(nongenerics($right))
            && !type_check($left, 'array')
        ) {
            // and the same for the right side
            Log::err(
                Log::ETYPE,
                "$left to array comparison",
                $context->getFile(),
                $node->lineno
            );
        }
        return new UnionType(['bool']);
    }

    public function visitBinaryIsIdentical(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsNotIdentical(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsEqual(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsNotEqual(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsSmaller(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsSmallerOrEqual(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsGreater(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }

    public function visitBinaryIsGreaterOrEqual(Node $node) {
        return $this->visitBinaryOpCommon($node);
    }


    public function visitBinaryAdd(Node $node) {
        $left =
            UnionType::fromNode($this->context, $node->children[0]);

        $right =
            UnionType::fromNode($this->context, $node->chilren[1]);

        // fast-track common cases
        if($left=='int' && $right == 'int') {
            return new UnionType(['int']);
        }
        if(($left=='int' || $left=='float') && ($right=='int' || $right=='float')) {
            return new UnionType(['float']);
        }

        $left_is_array = (!empty(generics($left)) && empty(nongenerics($left)));
        $right_is_array = (!empty(generics($right)) && empty(nongenerics($right)));

        if($left_is_array && !type_check($right, 'array')) {
            Log::err(
                Log::ETYPE,
                "invalid operator: left operand is array and right is not",
                $context->getFile(),
                $node->lineno
            );
            return new UnionType();
        } else if($right_is_array
            && !type_check($left, 'array')
        ) {
            Log::err(
                Log::ETYPE,
                "invalid operator: right operand is array and left is not",
                $file,
                $node->lineno
            );
            return new UnionType();
        } else if($left_is_array || $right_is_array) {
            // If it is a '+' and we know one side is an array and the other is unknown, assume array
            return new UnionType(['array']);
        }

        return new UnionType(['int', 'float']);
    }

    public function visit(Node $node) {
        $left =
            UnionType::fromNode(
                $this->context,
                $node->children[0]
            );

        $right =
            UnionType::fromNode($this->context, $node->children[1]);

        if ($left->hasTypeName('array')
            || $right->hasTypeName('array')
        ) {
            Log::err(
                Log::ETYPE,
                "invalid array operator",
                $context->getFile(),
                $node->lineno
            );
            return new UnionType();
        } else if ($left->hasTypeName('int')
            && $right->hasTypeName('int')
        ) {
            return new UnionType(['int']);
        } else if ($left->hasTypeName('float')
            && $right->hasTypeName('float')
        ) {
            return new UnionType(['float']);
        }

        return new UnionType(['int', 'float']);
    }

}
