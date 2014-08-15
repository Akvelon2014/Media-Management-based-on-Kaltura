/*
 * RED5 Open Source Flash Server - http://code.google.com/p/red5/
 * 
 * Copyright 2006-2012 by respective authors (see below). All rights reserved.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package org.red5.server;

import org.red5.logging.Red5LoggerFactory;
import org.red5.server.api.Red5;
import org.slf4j.Logger;
import org.slf4j.bridge.SLF4JBridgeHandler;
import org.springframework.context.support.FileSystemXmlApplicationContext;

/**
 * Launches Red5.
 *
 * @author The Red5 Project (red5@osflash.org)
 * @author Paul Gregoire (mondain@gmail.com)
 */
public class Launcher {

	/*
	static {
		ClassLoader tcl = Thread.currentThread().getContextClassLoader();		
		System.out.printf("[Launcher] Classloaders:\nSystem %s\nParent %s\nThis class %s\nTCL %s\n\n", ClassLoader.getSystemClassLoader(), tcl.getParent(), Launcher.class.getClassLoader(), tcl);
	}
	*/
	
	/**
	 * Launch Red5 under it's own classloader
	 */
	public void launch() {
		System.out.printf("Root: %s\nDeploy type: %s\nLogback selector: %s\n", System.getProperty("red5.root"), System.getProperty("red5.deployment.type"),
				System.getProperty("logback.ContextSelector"));
		try {
			//install the slf4j bridge (mostly for JUL logging)
			SLF4JBridgeHandler.install();
			//we create the logger here so that it is instanced inside the expected 
			//classloader
			Logger log = Red5LoggerFactory.getLogger(Launcher.class);
			//version info banner
			log.info("{} (http://code.google.com/p/red5/)", Red5.getVersion());
			//pimp red5
			System.out.printf("%s (http://code.google.com/p/red5/)\n", Red5.getVersion());
			//create red5 app context
			FileSystemXmlApplicationContext ctx = new FileSystemXmlApplicationContext(new String[] { "classpath:/red5.xml" }, false);
			//set the current threads classloader as the loader for the factory/appctx
			ctx.setClassLoader(Thread.currentThread().getContextClassLoader());
			//refresh must be called before accessing the bean factory
			ctx.refresh();
			/*
			if (log.isTraceEnabled()) {
				String[] names = ctx.getBeanDefinitionNames();
				for (String name : names) {
					log.trace("Bean name: {}", name);
				}
			}
			*/
		} catch (Throwable t) {
			System.out.printf("Exception %s\n", t);
			System.exit(9999);
		}
	}
	
}
