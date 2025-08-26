'use client'

import Link from 'next/link'
import { useAuth } from '@/contexts/AuthContext'
import { Car, User, LogOut, Plus } from 'lucide-react'

export default function Navbar() {
  const { user, logout } = useAuth()

  return (
    <nav className="bg-white shadow-lg">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between h-16">
          <div className="flex items-center">
            <Link href="/" className="flex items-center space-x-2">
              <Car className="h-8 w-8 text-blue-600" />
              <span className="text-xl font-bold text-gray-900">CarPlatform</span>
            </Link>
          </div>

          <div className="flex items-center space-x-4">
            <Link
              href="/"
              className="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
            >
              Voitures
            </Link>

            {user ? (
              <>
                {user.role === 'AGENCY' && (
                  <>
                    <Link
                      href="/dashboard"
                      className="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                    >
                      Tableau de bord
                    </Link>
                    <Link
                      href="/add-car"
                      className="bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 flex items-center space-x-1"
                    >
                      <Plus className="h-4 w-4" />
                      <span>Ajouter</span>
                    </Link>
                  </>
                )}

                <div className="flex items-center space-x-2">
                  <User className="h-5 w-5 text-gray-400" />
                  <span className="text-sm text-gray-700">{user.name}</span>
                </div>

                <button
                  onClick={logout}
                  className="text-gray-700 hover:text-red-600 px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-1"
                >
                  <LogOut className="h-4 w-4" />
                  <span>Déconnexion</span>
                </button>
              </>
            ) : (
              <div className="flex items-center space-x-2">
                <Link
                  href="/login"
                  className="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium"
                >
                  Connexion
                </Link>
                <Link
                  href="/register"
                  className="bg-blue-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
                >
                  Inscription
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </nav>
  )
}