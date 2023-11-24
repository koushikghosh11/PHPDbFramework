## Leetcode - 160 Intersection of Two Linked Lists Using Two Pointer:

We can use TwoPointerTwo PointerTwoPointer algorithm to find intersected node.

## Mathematics:
Consider the following Linked List(LL) Structure:

`A-->B-->C-->D--|`

`...............|-->E-->F-->G`

`.......H-->L-->|`

Here, head of first LL is `A` and head of second LL is `H`
Both intersect at node `E`

Let distance from node `A` to `D` is `x` and distance from node `H` to `L` is `y`.
Distance from node `E` to `G` is common and it is `z`.

During first traversal of LL1 and LL2, they will cover distance:
for LL1 pointer --> `x+z`
for LL2 pointer --> `y+z`

When the pointers inerchange their position in second traversal then distance covered till D and L are same :
for LL1 pointer --> `x+z+y`
for LL2 pointer --> `y+z+x`

if they will intersect then there exist a node that is common.

## Complexity

- Time Complexity: O(n+m)O(n+m)O(n+m)
- Space Complexity: O(1)O(1)O(1)

## Code
```java
 public ListNode getIntersectionNode(ListNode headA, ListNode headB) {
    ListNode node1 = headA, node2 = headB;
    int count = 0; // maintain count of traversal
    while(count<2){
        if(node1==node2) return node1;
        if(node1.next==null) ++count; // if no match after traversing twice then LL will not intersect.
        node1 = node1.next==null?headB:node1.next;
        node2 = node2.next==null?headA:node2.next;
    }

    return null;
}
```
