// Compilar con:
// g++ -std=gnu++0x -o templ templ.cpp

#include <iostream>

using namespace std;

struct Zero { enum { value = 0 }; };
template<typename T> struct Succ { enum { value = T::value + 1 }; };



template<typename X, typename XS> struct Cons {
        enum { x = X::value };
        typedef XS xs;
};

struct Empty {};



template<typename T> struct print_template_list {
        static void run() {
                cout << T::x << " ";
                print_template_list<typename T::xs>::run();
        }
};

template <> struct print_template_list<Empty> {
        static void run() {
                cout << endl;
        }
};



int main(int argc, char **argv) {
        print_template_list< Cons<Zero, Empty> >::run();
        print_template_list< Cons<Zero, Cons<Succ<Zero>, Empty> > >::run();
}
