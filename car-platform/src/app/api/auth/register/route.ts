import { NextRequest, NextResponse } from 'next/server'
import { prisma } from '@/lib/prisma'
import { hashPassword, generateToken } from '@/lib/auth'

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { email, password, name, role, phone, agencyData } = body

    // Vérifier si l'utilisateur existe déjà
    const existingUser = await prisma.user.findUnique({
      where: { email }
    })

    if (existingUser) {
      return NextResponse.json(
        { error: 'Un utilisateur avec cet email existe déjà' },
        { status: 400 }
      )
    }

    // Hasher le mot de passe
    const hashedPassword = await hashPassword(password)

    // Créer l'utilisateur
    const user = await prisma.user.create({
      data: {
        email,
        password: hashedPassword,
        name,
        role,
        phone
      }
    })

    // Si c'est une agence, créer les données de l'agence
    if (role === 'AGENCY' && agencyData) {
      await prisma.agency.create({
        data: {
          name: agencyData.name,
          description: agencyData.description,
          address: agencyData.address,
          phone: agencyData.phone,
          email: agencyData.email,
          website: agencyData.website,
          ownerId: user.id
        }
      })
    }

    // Générer le token JWT
    const token = generateToken({
      userId: user.id,
      email: user.email,
      role: user.role
    })

    return NextResponse.json({
      message: 'Utilisateur créé avec succès',
      token,
      user: {
        id: user.id,
        email: user.email,
        name: user.name,
        role: user.role
      }
    })

  } catch (error) {
    console.error('Erreur lors de l\'inscription:', error)
    return NextResponse.json(
      { error: 'Erreur interne du serveur' },
      { status: 500 }
    )
  }
}