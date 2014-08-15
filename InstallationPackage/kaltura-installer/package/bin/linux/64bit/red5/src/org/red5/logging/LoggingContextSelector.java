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

package org.red5.logging;

import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ConcurrentHashMap;
import java.util.concurrent.ConcurrentMap;

import ch.qos.logback.classic.LoggerContext;
import ch.qos.logback.classic.joran.JoranConfigurator;
import ch.qos.logback.classic.selector.ContextSelector;
import ch.qos.logback.classic.util.ContextInitializer;
import ch.qos.logback.core.joran.spi.JoranException;
import ch.qos.logback.core.util.Loader;
import ch.qos.logback.core.util.StatusPrinter;

/**
 * A class that allows the LoggerFactory to access an web context based LoggerContext.
 * 
 * Add this java option -Dlogback.ContextSelector=org.red5.logging.LoggingContextSelector
 * 
 * @author Paul Gregoire (mondain@gmail.com)
 */
public class LoggingContextSelector implements ContextSelector {

	private static final ConcurrentMap<String, LoggerContext> contextMap = new ConcurrentHashMap<String, LoggerContext>();

	private final ThreadLocal<LoggerContext> threadLocal = new ThreadLocal<LoggerContext>();

	private final LoggerContext defaultContext;

	private volatile String contextName;

	private volatile String contextConfigFile;

	public LoggingContextSelector(LoggerContext context) {
		System.out.printf("Setting default logging context: %s\n", context.getName());
		defaultContext = context;
	}

	public LoggerContext getLoggerContext() {
		//System.out.println("getLoggerContext request");		
		// First check if ThreadLocal has been set already
		LoggerContext lc = threadLocal.get();
		if (lc != null) {
			//System.out.printf("Thread local found: %s\n", lc.getName());
			return lc;
		}

		if (contextName == null) {
			//System.out.println("Context name was null, returning default");
			// We return the default context
			return defaultContext;
		} else {
			// Let's see if we already know such a context
			LoggerContext loggerContext = contextMap.get(contextName);
			//System.out.printf("Logger context for %s is %s\n", contextName, loggerContext);

			if (loggerContext == null) {
				// We have to create a new LoggerContext
				loggerContext = new LoggerContext();
				loggerContext.setName(contextName);

				// allow override using logbacks system prop
				String overrideProperty = System.getProperty("logback.configurationFile");
				if (overrideProperty == null) {
					contextConfigFile = String.format("logback-%s.xml", contextName);
				} else {
					contextConfigFile = String.format(overrideProperty, contextName);
				}
				System.out.printf("Context logger config file: %s\n", contextConfigFile);

				ClassLoader classloader = Thread.currentThread().getContextClassLoader();
				//System.out.printf("Thread context cl: %s\n", classloader);
				//ClassLoader classloader2 = Loader.class.getClassLoader();
				//System.out.printf("Loader tcl: %s\n", classloader2);

				//URL url = Loader.getResourceBySelfClassLoader(contextConfigFile);
				URL url = Loader.getResource(contextConfigFile, classloader);
				if (url != null) {
					try {
						JoranConfigurator configurator = new JoranConfigurator();
						loggerContext.reset();
						configurator.setContext(loggerContext);
						configurator.doConfigure(url);
					} catch (JoranException e) {
						StatusPrinter.print(loggerContext);
					}
				} else {
					try {
						ContextInitializer ctxInit = new ContextInitializer(loggerContext);
						ctxInit.autoConfig();
					} catch (JoranException je) {
						StatusPrinter.print(loggerContext);
					}
				}

				System.out.printf("Adding logger context: %s to map for context: %s\n", loggerContext.getName(), contextName);
				contextMap.put(contextName, loggerContext);
			}
			return loggerContext;
		}
	}

	public LoggerContext getLoggerContext(String name) {
		//System.out.printf("getLoggerContext request for %s\n", name);
		//System.out.printf("Context is in map: %s\n", contextMap.containsKey(name));
		return contextMap.get(name);
	}

	public LoggerContext getDefaultLoggerContext() {
		return defaultContext;
	}

	public void attachLoggerContext(String contextName, LoggerContext loggerContext) {
		contextMap.put(contextName, loggerContext);
	}

	public LoggerContext detachLoggerContext(String loggerContextName) {
		return contextMap.remove(loggerContextName);
	}

	public List<String> getContextNames() {
		List<String> list = new ArrayList<String>();
		list.addAll(contextMap.keySet());
		return list;
	}

	public void setContextName(String contextName) {
		this.contextName = contextName;
	}

	public void setContextConfigFile(String contextConfigFile) {
		this.contextConfigFile = contextConfigFile;
	}

	/**
	 * Returns the number of managed contexts Used for testing purposes
	 * 
	 * @return the number of managed contexts
	 */
	public int getCount() {
		return contextMap.size();
	}

	/**
	 * These methods are used by the LoggerContextFilter.
	 * 
	 * They provide a way to tell the selector which context to use, thus saving
	 * the cost of a JNDI call at each new request.
	 * 
	 * @param context logging context
	 */
	public void setLocalContext(LoggerContext context) {
		threadLocal.set(context);
	}

	public void removeLocalContext() {
		threadLocal.remove();
	}

}
