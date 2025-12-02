 // @ts-nocheck


// function Animal(name, age) {
//     this.name = name;
//     this.age = age;
// }
// Animal.prototype.speak = function() { console.log("grr"); };

// let spot = new Animal("Spot", 3);
// spot.speak();

class MyElement extends HTMLElement {
    #internals
    constructor() {
        super()
        this.#internals = this.attachInternals();
    }

    connectedCallback() {
        const shadow = this.attachShadow({ mode: "open" });
        let body = document.createElement("div");
        let style = document.createElement('style')
        style.textContent = `
        span.count {
            color: blue;
        }
        div {
            border: 2px solid white;
        }
        `
        let countSpan = document.createElement('span')
        countSpan.classList.add('count')
        countSpan.textContent = ' (' + this.textContent.split(' ').length + ' words)'
        if (this.attributes.getNamedItem("before")) {
            body.append(this.attributes.getNamedItem("before").value)
        }
        let slot = document.createElement('slot')
        body.append(slot)
        body.append(countSpan)
        shadow.appendChild(style);
        shadow.appendChild(body);
    }
}
// 		<my-element before="Counting words: ">This is an example of using my <i>custom</i> element.</my-element>
window.customElements.define("my-element", MyElement)