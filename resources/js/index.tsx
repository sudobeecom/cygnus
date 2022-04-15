import React from "react";
import { render } from "react-dom";
import { InertiaApp } from "@inertiajs/inertia-react";
import { InertiaProgress } from "@inertiajs/progress";
import "./i18n";
import StructuredPage from "./Pages/StructuredPage";

const element = document.getElementById("app");

if (element && element.dataset.page) {
	render(
		<InertiaApp
			initialPage={JSON.parse(element.dataset.page)}
			resolveComponent={() => StructuredPage}
		/>,
		element
	);
}

InertiaProgress.init({ color: "#4B5563" });
