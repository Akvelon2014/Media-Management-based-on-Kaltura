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

package org.red5.server.persistence;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.lang.reflect.Constructor;
import java.lang.reflect.InvocationTargetException;

import org.apache.mina.core.buffer.IoBuffer;
import org.red5.io.amf.Input;
import org.red5.io.amf.Output;
import org.red5.io.object.Deserializer;
import org.red5.server.api.persistence.IPersistable;
import org.red5.server.api.scope.IScope;
import org.red5.server.net.servlet.ServletUtils;
import org.red5.server.so.SharedObject;
import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.springframework.core.io.Resource;
import org.springframework.core.io.support.ResourcePatternResolver;
import org.springframework.web.context.support.ServletContextResource;

/**
 * Simple file-based persistence for objects. Lowers memory usage if used instead of RAM memory storage.
 * 
 * @author The Red5 Project (red5@osflash.org)
 * @author Joachim Bauch (jojo@struktur.de)
 */
public class FilePersistence extends RamPersistence {

	/**
	 * Logger
	 */
	private Logger log = LoggerFactory.getLogger(FilePersistence.class);

	/**
	 * Files path
	 */
	private String path = "persistence";

	/**
	 * Root directory under file storage path
	 */
	private String rootDir = "";

	/**
	 * File extension for persistent objects
	 */
	private String extension = ".red5";

	/**
	 * Whether there's need to check for empty directories
	 */
	// TODO: make this configurable
	private boolean checkForEmptyDirectories = true;

	/**
	 * Thread to serialize persistent objects.
	 */
	private FilePersistenceThread storeThread = null;

	/**
	 * Create file persistence object from given resource pattern resolver
	 * @param resolver            Resource pattern resolver and loader
	 */
	public FilePersistence(ResourcePatternResolver resolver) {
		super(resolver);
		setPath(path);
	}

	/**
	 * Create file persistence object for given scope
	 * @param scope               Scope
	 */
	public FilePersistence(IScope scope) {
		super(scope);
		setPath(path);
	}

	/**
	 * Setter for file path.
	 *
	 * @param path  New path
	 */
	public void setPath(String path) {
		log.debug("Set path: {}", path);
		Resource rootFile = resources.getResource(path);
		try {
			// check for existence
			if (!rootFile.exists()) {
				log.debug("Persistence directory does not exist");
				if (rootFile instanceof ServletContextResource) {
					ServletContextResource servletResource = (ServletContextResource) rootFile;
					String contextPath = servletResource.getServletContext().getContextPath();
					log.debug("Persistence context path: {}", contextPath);
					if ("/".equals(contextPath)) {
						contextPath = "/root";
					}
					rootDir = String.format("%s/webapps%s/persistence", System.getProperty("red5.root"), contextPath);
					log.debug("Persistence directory path: {}", rootDir);
					File persistDir = new File(rootDir);
					if (!persistDir.mkdir()) {
						log.warn("Persistence directory creation failed");
					} else {
						log.debug("Persistence directory access - read: {} write: {}", persistDir.canRead(), persistDir.canWrite());
					}
					persistDir = null;
				}
			} else {
				rootDir = rootFile.getFile().getAbsolutePath();
			}
			log.debug("Root dir: {} path: {}", rootDir, path);
			this.path = path;
		} catch (IOException err) {
			log.error("I/O exception thrown when setting file path to {}", path, err);
			throw new RuntimeException(err);
		}
		storeThread = FilePersistenceThread.getInstance();
	}

	/**
	 * Setter for extension.
	 *
	 * @param extension  New extension.
	 */
	public void setExtension(String extension) {
		this.extension = extension;
	}

	/**
	 * Return file path for persistable object
	 * @param object          Object to obtain file path for
	 * @return                Path on disk
	 */
	private String getObjectFilepath(IPersistable object) {
		return getObjectFilepath(object, false);
	}

	/**
	 * Return file path for persistable object
	 * @param object          Object to obtain file path for
	 * @param completePath    Whether it full path full path sould be returned
	 * @return                Path on disk
	 */
	private String getObjectFilepath(IPersistable object, boolean completePath) {
		StringBuilder result = new StringBuilder(path);
		result.append('/');
		result.append(object.getType());
		result.append('/');

		String objectPath = object.getPath();
		log.debug("Object path: {}", objectPath);
		result.append(objectPath);
		if (!objectPath.endsWith("/")) {
			result.append('/');
		}

		if (completePath) {
			String name = object.getName();
			log.debug("Object name: {}", name);
			int pos = name.lastIndexOf('/');
			if (pos >= 0) {
				result.append(name.substring(0, pos));
			}
		}

		//fix up path
		int idx = -1;
		if (File.separatorChar != '/') {
			while ((idx = result.indexOf(File.separator)) != -1) {
				result.deleteCharAt(idx);
				result.insert(idx, '/');
			}
		}
		if (log.isDebugEnabled()) {
			log.debug("Path step 1: {}", result.toString());
		}
		//remove any './'
		if ((idx = result.indexOf("./")) != -1) {
			result.delete(idx, idx + 2);
		}
		if (log.isDebugEnabled()) {
			log.debug("Path step 2: {}", result.toString());
		}
		//remove any '//'
		while ((idx = result.indexOf("//")) != -1) {
			result.deleteCharAt(idx);
		}
		if (log.isDebugEnabled()) {
			log.debug("Path step 3: {}", result.toString());
		}

		return result.toString();
	}

	/** {@inheritDoc} */
	@Override
	protected String getObjectPath(String id, String name) {
		if (id.startsWith(path)) {
			id = id.substring(path.length() + 1);
		}
		return super.getObjectPath(id, name);
	}

	/**
	 * Get filename for persistable object
	 * @param object          Persistable object
	 * @return                Name of file where given object is persisted to
	 */
	private String getObjectFilename(IPersistable object) {
		String path = getObjectFilepath(object);
		String name = object.getName();
		if (name == null) {
			name = PERSISTENCE_NO_NAME;
		}
		return path + name + extension;
	}

	/**
	 * Load resource with given name
	 * @param name             Resource name
	 * @return                 Persistable object
	 */
	private IPersistable doLoad(String name) {
		return doLoad(name, null);
	}

	/**
	 * Load resource with given name and attaches to persistable object
	 * @param name             Resource name
	 * @param object           Object to attach to
	 * @return                 Persistable object
	 */
	private IPersistable doLoad(String name, IPersistable object) {
		IPersistable result = object;
		Resource data = resources.getResource(name);
		if (data == null || !data.exists()) {
			// No such file
			return null;
		}

		FileInputStream input;
		String filename;
		try {
			File fp = data.getFile();
			if (fp.length() == 0) {
				// File is empty
				log.error("The file at {} is empty.", data.getFilename());
				return null;
			}

			filename = fp.getAbsolutePath();
			input = new FileInputStream(filename);
		} catch (FileNotFoundException e) {
			log.error("The file at {} does not exist.", data.getFilename());
			return null;
		} catch (IOException e) {
			log.error("Could not load file from {}.", data.getFilename(), e);
			return null;
		}

		try {
			IoBuffer buf = IoBuffer.allocate(input.available());
			try {
				ServletUtils.copy(input, buf.asOutputStream());
				buf.flip();
				Input in = new Input(buf);
				Deserializer deserializer = new Deserializer();
				String className = deserializer.deserialize(in, String.class);
				if (result == null) {
					// we need to create the object first
					try {
						Class<?> theClass = Class.forName(className);
						Constructor<?> constructor = null;
						try {
							// try to create object by calling constructor with Input stream as parameter
							for (Class<?> interfaceClass : in.getClass().getInterfaces()) {
								constructor = theClass.getConstructor(new Class[] { interfaceClass });
								if (constructor != null) {
									break;
								}
							}
							if (constructor == null) {
								throw new NoSuchMethodException();
							}
							result = (IPersistable) constructor.newInstance(in);
						} catch (NoSuchMethodException err) {
							// no valid constructor found, use empty constructor
							result = (IPersistable) theClass.newInstance();
							result.deserialize(in);
						} catch (InvocationTargetException err) {
							// error while invoking found constructor, use empty constructor
							result = (IPersistable) theClass.newInstance();
							result.deserialize(in);
						}
					} catch (ClassNotFoundException cnfe) {
						log.error("Unknown class {}", className);
						return null;
					} catch (IllegalAccessException iae) {
						log.error("Illegal access", iae);
						return null;
					} catch (InstantiationException ie) {
						log.error("Could not instantiate class {}", className);
						return null;
					}
					// set object's properties
					result.setName(getObjectName(name));
					result.setPath(getObjectPath(name, result.getName()));
				} else {
					// Initialize existing object
					String resultClass = result.getClass().getName();
					if (!resultClass.equals(className)) {
						log.error("The classes differ: {} != {}", resultClass, className);
						return null;
					}
					result.deserialize(in);
				}
			} finally {
				buf.free();
				buf = null;
			}
			if (result.getStore() != this) {
				result.setStore(this);
			}
			super.save(result);
			log.debug("Loaded persistent object {} from {}", result, filename);
		} catch (IOException e) {
			log.error("Could not load file at {}", filename);
			return null;
		}
		return result;
	}

	/** {@inheritDoc} */
	@Override
	public IPersistable load(String name) {
		IPersistable result = super.load(name);
		if (result != null) {
			// Object has already been loaded
			return result;
		}
		return doLoad(path + '/' + name + extension);
	}

	/** {@inheritDoc} */
	@Override
	public boolean load(IPersistable object) {
		if (object.isPersistent()) {
			// Already loaded
			return true;
		}
		return (doLoad(getObjectFilename(object), object) != null);
	}

	/**
	 * Save persistable object
	 * @param object           Persistable object
	 * @return                 <code>true</code> on success, <code>false</code> otherwise
	 */
	protected boolean saveObject(IPersistable object) {
		boolean result = true;
		String path = getObjectFilepath(object, true);
		log.debug("Path: {}", path);
		Resource resPath = resources.getResource(path);
		boolean exists = resPath.exists();
		log.debug("Resource (dir) check #1 - file name: {} exists: {}", resPath.getFilename(), exists);
		File dir = null;
		try {
			if (!exists) {
				resPath = resources.getResource("classpath:" + path);
				exists = resPath.exists();
				log.debug("Resource (dir) check #2 - file name: {} exists: {}", resPath.getFilename(), exists);
				if (!exists) {
					StringBuilder root = new StringBuilder(rootDir);
					//fix up path
					int idx = -1;
					if (File.separatorChar != '/') {
						while ((idx = root.indexOf(File.separator)) != -1) {
							root.deleteCharAt(idx);
							root.insert(idx, '/');
						}
					}
					resPath = resources.getResource("file://" + root.toString() + path.substring(11));
					exists = resPath.exists();
					log.debug("Resource (dir) check #3 - file name: {} exists: {}", resPath.getFilename(), exists);
				}
			}
			dir = resPath.getFile();
			if (!dir.isDirectory() && !dir.mkdirs()) {
				log.error("Could not create directory {}", dir.getAbsolutePath());
				result = false;
			}
		} catch (IOException err) {
			log.error("Could not create resource file for path {}", path, err);
			err.printStackTrace();
			result = false;
		}

		//if we made it this far and everything seems ok
		if (result) {
			// if it's a persistent SharedObject and it's empty don't write it to disk. APPSERVER-364
			if (object instanceof SharedObject) {
				SharedObject soRef = (SharedObject) object;
				if (soRef.getAttributes().size() == 0) {
					// return true to trick the server into thinking everything is just fine :P
					return true;
				}
			}
			String filename = getObjectFilename(object);
			log.debug("File name: {}", filename);
			//strip path
			if (filename.indexOf('/') != -1) {
				filename = filename.substring(filename.lastIndexOf('/'));
				log.debug("New file name: {}", filename);
			}
			File file = new File(dir, filename);
			//Resource resFile = resources.getResource(filename);
			//log.debug("Resource (file) check #1 - file name: {} exists: {}", resPath.getFilename(), exists);
			IoBuffer buf = null;
			try {
				int initialSize = 8192;
				if (file.exists()) {
					// We likely also need the original file size when writing object
					initialSize += (int) file.length();
				}
				buf = IoBuffer.allocate(initialSize);
				buf.setAutoExpand(true);
				Output out = new Output(buf);
				out.writeString(object.getClass().getName());
				object.serialize(out);
				buf.flip();

				FileOutputStream output = new FileOutputStream(file.getAbsolutePath());
				ServletUtils.copy(buf.asInputStream(), output);
				output.close();
				log.debug("Stored persistent object {} at {}", object, filename);
			} catch (IOException e) {
				log.error("Could not create / write file {}", filename, e);
				log.warn("Exception {}", e);
				result = false;
			} finally {
				if (buf != null) {
					buf.free();
					buf = null;
				}
				file = null;
				dir = null;
			}
		}
		return result;
	}

	/** {@inheritDoc} */
	@Override
	public boolean save(IPersistable object) {
		if (!super.save(object)) {
			return false;
		}
		storeThread.modified(object, this);
		return true;
	}

	/**
	 * Remove empty dirs
	 * @param base             Base directory
	 */
	protected void checkRemoveEmptyDirectories(String base) {
		if (!checkForEmptyDirectories) {
			return;
		}
		String dir;
		Resource resFile = resources.getResource(base.substring(0, base.lastIndexOf('/')));
		try {
			dir = resFile.getFile().getAbsolutePath();
		} catch (IOException err) {
			return;
		}

		while (!dir.equals(rootDir)) {
			File fp = new File(dir);
			if (!fp.isDirectory()) {
				// This should never happen
				break;
			}
			if (fp.list().length != 0) {
				// Directory is not empty
				break;
			}
			if (!fp.delete()) {
				// Could not remove directory
				break;
			}
			// Move up one directory
			dir = fp.getParent();
		}
	}

	/** {@inheritDoc} */
	@Override
	public boolean remove(String name) {
		super.remove(name);
		String filename = path + '/' + name + extension;
		Resource resFile = resources.getResource(filename);
		if (!resFile.exists()) {
			// File already deleted
			return true;
		}
		try {
			boolean result = resFile.getFile().delete();
			if (result) {
				checkRemoveEmptyDirectories(filename);
			}
			return result;
		} catch (IOException err) {
			return false;
		}
	}

	/** {@inheritDoc} */
	@Override
	public boolean remove(IPersistable object) {
		return remove(getObjectId(object));
	}

	/** {@inheritDoc} */
	@Override
	public void notifyClose() {
		// Write any pending objects
		storeThread.notifyClose(this);
		super.notifyClose();
	}

}
