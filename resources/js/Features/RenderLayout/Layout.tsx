import React, { useEffect } from "react";
import { useTesting } from "../../hooks/useTesting";
import { Notifications } from "../Notifications/Notifications";
import { LayoutCommonInterface } from "./LayoutCommonInterface";

export const Layout: React.FC<LayoutCommonInterface> = ({
	title,
	children,
}) => {
	useTesting();

	useEffect(() => {
		if (title !== null) {
			document.title = `${title} - Lyra`;
		}
	}, [title]);

	return <Notifications>{children}</Notifications>;
};
