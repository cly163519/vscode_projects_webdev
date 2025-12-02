let target = Math.floor(Math.random() * 100)

document.getElementById('make_guess').
	addEventListener("click", function() {
		let guess = document.getElementById('guess').value
		if (guess < target)
			alert("Too small");
		else if (guess > target) {
			alert("Too big!")
		} else
			alert("Correct")
})

let obj = {
	name: "world",
	hello: function() {
		console.log("Hello, " + this.name)
	}
}

obj.hello()
obj["hello"]()

//obj["hello"]()=function() {console.log("goodbye")}
//obj.hello()

function Greeter(who) {
	this.name = who
}
Greeter.prototype = obj;
Greeter.prototype.farewell = function() {}

let g = new Greeter("SWEN504")
g.hello()

class Greeter2 extends Greeter {
	#id
	constructor() {
		super("everybody")
		this.#id = 1
	}
	farewell() {

	}
}

let g2 = new Greeter2()
g2.hello()

let a = [1, "2", true]

console.log(a[1])
a[1] = "hello"
a.push(123)

for (let o of a) {	
	console.log(o)
}

for (let i in a) {
	//console.log(i)
}

for(let i=0; i<a.length; i++) {
	//console.log(a[i])
}
